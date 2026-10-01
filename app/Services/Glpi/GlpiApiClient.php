<?php

namespace App\Services\Glpi;

use App\Models\GlpiInstance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Talks to one registered GlpiInstance's REST API v2 ("High-Level API").
 * OAuth2 password grant (POST {base_url}/api.php/token) - the access token
 * is cached per instance (TTL matched to the token's own expiry, minus a
 * safety margin) so a whole CSV import doesn't re-authenticate per row.
 *
 * Asset itemtypes live under two different route prefixes depending on
 * GLPI's own module split - see ASSET_ITEMTYPES/MANAGEMENT_ITEMTYPES below,
 * both confirmed against this app's actual GLPI 11 source
 * (Glpi\Api\HL\Controller\AssetController::getAssetTypes() and
 * ManagementController's schema list). Everything else (Location,
 * Manufacturer, *Type, *Model...) is a generic dropdown under /Dropdowns/.
 */
class GlpiApiClient
{
    private const ASSET_ITEMTYPES = [
        'Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
        'Printer', 'SoftwareLicense', 'Certificate', 'Unmanaged', 'Appliance',
    ];

    private const MANAGEMENT_ITEMTYPES = [
        'Line', 'Domain', 'License', 'Supplier',
    ];

    public function __construct(private GlpiInstance $instance)
    {
    }

    /**
     * @throws RuntimeException on any auth failure - callers decide whether
     *                          to surface it as-is (e.g. "Testar Conexão")
     *                          or wrap it with row-level context.
     */
    public function authenticate(): string
    {
        $cacheKey = "glpi_instance_token_{$this->instance->id}";

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = Http::asForm()->post(rtrim($this->instance->base_url, '/') . '/api.php/token', [
                'grant_type' => 'password',
                'client_id' => $this->instance->oauth_client_id,
                'client_secret' => $this->instance->oauth_client_secret,
                'username' => $this->instance->username,
                'password' => $this->instance->password,
                'scope' => 'api',
            ]);

            if (! $response->successful() || blank($response->json('access_token'))) {
                throw new RuntimeException(
                    'Falha ao autenticar no GLPI: ' . ($response->json('detail') ?? $response->json('title') ?? $response->body())
                );
            }

            return $response->json('access_token');
        });
    }

    public function testConnection(): array
    {
        try {
            Cache::forget("glpi_instance_token_{$this->instance->id}");
            $this->authenticate();

            return ['success' => true, 'message' => 'Conexão realizada com sucesso.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Finds a dropdown row by exact name (RSQL `name==`), creating it if
     * missing. Used for Location/State/Manufacturer/*Type/*Model - none of
     * these exist yet on a fresh GLPI instance, so "create if missing" is
     * the common path, not the exception.
     */
    public function findOrCreateDropdown(string $itemtype, string $name, array $extra = []): int
    {
        $name = trim($name);

        $existing = $this->findOneByField("Dropdowns/{$itemtype}", 'name', $name);
        if ($existing !== null) {
            return $existing['id'];
        }

        $created = $this->post("Dropdowns/{$itemtype}", array_merge(['name' => $name], $extra));

        return $created['id'];
    }

    /**
     * RSQL-filtered lookup on an exact field match, quoting the value so
     * names containing reserved RSQL characters (`,;()` - a real case from
     * the InvGate CSV: a model literally named "VMware7,1") don't break
     * the filter grammar.
     *
     * @return array|null the first matching record, or null if none found
     */
    public function findOneByField(string $resourcePath, string $field, string $value): ?array
    {
        $quoted = '"' . str_replace('"', '\\"', $value) . '"';

        $response = $this->request('get', $resourcePath, [
            'filter' => "{$field}=={$quoted}",
            'limit' => 1,
        ]);

        $results = is_array($response) && array_is_list($response) ? $response : ($response['items'] ?? []);

        return $results[0] ?? null;
    }

    public function createAsset(string $itemtype, array $data): array
    {
        return $this->post($this->assetResourcePath($itemtype), $data);
    }

    public function updateAsset(string $itemtype, int $id, array $data): array
    {
        return $this->request('patch', $this->assetResourcePath($itemtype) . "/{$id}", $data);
    }

    /**
     * Idempotency lookup for an already-imported asset - matches on
     * `otherserial`, where App\Services\Glpi\GlpiAssetImportService stores
     * "INVGATE-{ID do recurso}".
     */
    public function findAssetByOtherSerial(string $itemtype, string $otherSerial): ?array
    {
        return $this->findOneByField($this->assetResourcePath($itemtype), 'otherserial', $otherSerial);
    }

    private function assetResourcePath(string $itemtype): string
    {
        if (in_array($itemtype, self::ASSET_ITEMTYPES, true)) {
            return "Assets/{$itemtype}";
        }

        if (in_array($itemtype, self::MANAGEMENT_ITEMTYPES, true)) {
            return "Management/{$itemtype}";
        }

        throw new RuntimeException("Itemtype GLPI desconhecido para import de ativos: {$itemtype}");
    }

    private function post(string $resourcePath, array $data): array
    {
        return $this->request('post', $resourcePath, $data);
    }

    private function request(string $method, string $resourcePath, array $payload = []): array
    {
        $token = $this->authenticate();
        $url = rtrim($this->instance->base_url, '/') . '/api.php/v2/' . ltrim($resourcePath, '/');

        $request = Http::withToken($token)->acceptJson();

        $response = match (strtolower($method)) {
            'get' => $request->get($url, $payload),
            'post' => $request->post($url, $payload),
            'patch' => $request->patch($url, $payload),
            'delete' => $request->delete($url, $payload),
            default => throw new RuntimeException("Método HTTP não suportado: {$method}"),
        };

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'GLPI API [%s %s] retornou %d: %s',
                strtoupper($method),
                $resourcePath,
                $response->status(),
                $response->json('detail') ?? $response->json('title') ?? $response->body()
            ));
        }

        return $response->json() ?? [];
    }
}
