<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Looks up a company's cadastral data by CNPJ via BrasilAPI (free, no API
 * key, sourced from Receita Federal's own public base) - used to auto-fill
 * the Cliente form (razão social, endereço completo, e-mail, telefone) so
 * cadastrar uma empresa não significa redigitar dados que já são públicos.
 *
 * Called from the admin app itself (not directly from the browser) so the
 * request isn't subject to BrasilAPI's CORS policy, and so a future swap to
 * a different provider only touches this one class.
 */
class CnpjLookupService
{
    public function lookup(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
        } catch (\Throwable $e) {
            Log::warning("Consulta de CNPJ {$cnpj} falhou: {$e->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
            'name' => $data['razao_social'] ?? null,
            'email' => $data['email'] ?: null,
            'phone' => $data['ddd_telefone_1'] ?: null,
            'postal_code' => $data['cep'] ?? null,
            'address' => $data['logradouro'] ?? null,
            'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['municipio'] ?? null,
            'state' => $data['uf'] ?? null,
            // Mesmo código usado pela NFS-e (ver nfse_municipio_codigo no
            // Cliente) - a BrasilAPI já devolve o IBGE de 7 dígitos certo,
            // poupando a busca manual que o campo hoje sugere fazer no site do IBGE.
            'nfse_municipio_codigo' => $data['codigo_municipio_ibge'] ?? null,
        ];
    }
}
