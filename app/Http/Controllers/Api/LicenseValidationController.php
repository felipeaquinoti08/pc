<?php

namespace App\Http\Controllers\Api;

use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class LicenseValidationController extends Controller
{
    /**
     * Called by a licensed plugin every ~10 minutes.
     *
     * Request:  {license_key, service_slug, instance_identifier, instance_url?}
     * Response: {valid: bool, reason: string, quantity?, active_installations?}
     *
     * reason is one of: ok, invalid_key, wrong_plugin, suspended, cancelled,
     * expired, limit_exceeded.
     *
     * service_slug is a fixed string each plugin hardcodes in its own
     * source (the Service record's `slug` in Painel Central, e.g.
     * "entra-sso") - without it, a license_key bought for one plugin would
     * validate fine when pasted into a completely different plugin, since
     * nothing else in this request ties the key to a specific product.
     *
     * `quantity` is enforced PER SERVICE, not shared across a whole
     * package - a license covering 3 services with quantity=1 allows that
     * one GLPI instance to activate all 3 plugins (one activation slot per
     * service), not just one of them total.
     */
    public function validateLicense(Request $request)
    {
        // Validação manual (em vez de deixar o validate() decidir sozinho)
        // pra garantir resposta em JSON sempre - um plugin que esquecer o
        // header Accept: application/json receberia um redirect HTML em
        // vez do {valid:false,...} documentado, já que esta rota não tem
        // sessão/view nenhuma por trás.
        try {
            $validated = $request->validate([
                'license_key' => ['required', 'string'],
                'service_slug' => ['required', 'string'],
                'instance_identifier' => ['required', 'string', 'max:191'],
                'instance_url' => ['nullable', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['valid' => false, 'reason' => 'invalid_request', 'errors' => $e->errors()], 422);
        }

        $license = License::with('services')->where('license_key', $validated['license_key'])->first();

        if (! $license) {
            return response()->json(['valid' => false, 'reason' => 'invalid_key'], 404);
        }

        // Pacote: a mesma license_key pode cobrir vários serviços/plugins -
        // basta o slug pedido estar entre os que esta licença contempla.
        $service = $license->services->firstWhere('slug', $validated['service_slug']);

        if (! $service) {
            return response()->json(['valid' => false, 'reason' => 'wrong_plugin'], 403);
        }

        if (in_array($license->status, [License::STATUS_SUSPENDED, License::STATUS_CANCELLED], true)) {
            return response()->json(['valid' => false, 'reason' => $license->status], 403);
        }

        if ($license->expires_at !== null && $license->expires_at->isPast() && ! $license->expires_at->isToday()) {
            return response()->json(['valid' => false, 'reason' => 'expired'], 403);
        }

        // A vaga ("quantity") vale por serviço dentro do pacote - uma
        // mesma instância consegue validar vários plugins do mesmo pacote
        // sem disputar a mesma vaga entre eles, então toda contagem/match
        // de ativação abaixo é restrita a este $service específico.
        $activation = LicenseActivation::where('license_id', $license->id)
            ->where('service_id', $service->id)
            ->where('instance_identifier', $validated['instance_identifier'])
            ->first();

        if ($activation && $activation->status === LicenseActivation::STATUS_ACTIVE) {
            $activation->update([
                'instance_url' => $validated['instance_url'] ?? $activation->instance_url,
                'ip_address' => $request->ip(),
                'last_seen_at' => now(),
            ]);

            return response()->json([
                'valid' => true,
                'reason' => 'ok',
                'quantity' => $license->quantity,
                'active_installations' => $license->activeActivations()->where('service_id', $service->id)->count(),
            ]);
        }

        // New instance (or a previously revoked one checking in again) -
        // only allowed if there's room under the contracted quantity, pra
        // este serviço especificamente.
        $activeCount = $license->activeActivations()->where('service_id', $service->id)->count();
        if ($activeCount >= $license->quantity) {
            return response()->json([
                'valid' => false,
                'reason' => 'limit_exceeded',
                'quantity' => $license->quantity,
                'active_installations' => $activeCount,
            ], 403);
        }

        LicenseActivation::updateOrCreate(
            [
                'license_id' => $license->id,
                'service_id' => $service->id,
                'instance_identifier' => $validated['instance_identifier'],
            ],
            [
                'instance_url' => $validated['instance_url'] ?? null,
                'ip_address' => $request->ip(),
                'status' => LicenseActivation::STATUS_ACTIVE,
                'activated_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'valid' => true,
            'reason' => 'ok',
            'quantity' => $license->quantity,
            'active_installations' => $activeCount + 1,
        ]);
    }
}
