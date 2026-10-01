<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'sso_tenant_id',
        'sso_client_id',
        'sso_client_secret',
        'blob_account_name',
        'blob_account_key',
        'blob_container',
        'blob_endpoint_suffix',
        'mp_environment',
        'mp_public_key',
        'mp_access_token',
        'mp_webhook_secret',
        'mp_test_public_key',
        'mp_test_access_token',
        'mp_test_webhook_secret',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'nfse_environment',
        'nfse_next_dps_number',
        'nfse_dps_serie',
        'nfse_cnpj',
        'nfse_inscricao_municipal',
        'nfse_op_simples_nacional',
        'nfse_reg_esp_trib',
        'nfse_telefone',
        'nfse_email',
        'nfse_municipio_codigo',
        'nfse_municipio_nome',
        'nfse_uf',
        'nfse_cep',
        'nfse_logradouro',
        'nfse_numero',
        'nfse_bairro',
        'nfse_codigo_tributacao_nacional',
        'nfse_codigo_tributacao_municipal',
        'nfse_codigo_nbs',
        'nfse_aliquota_iss',
        'nfse_aliquota_pis',
        'nfse_aliquota_cofins',
        'nfse_percentual_trib_federal',
        'nfse_certificate_path',
        'nfse_certificate_password',
    ];

    protected function casts(): array
    {
        return [
            'sso_client_secret' => 'encrypted',
            'blob_account_key' => 'encrypted',
            'mp_access_token' => 'encrypted',
            'mp_webhook_secret' => 'encrypted',
            'mp_test_access_token' => 'encrypted',
            'mp_test_webhook_secret' => 'encrypted',
            'mail_password' => 'encrypted',
            'nfse_certificate_password' => 'encrypted',
        ];
    }

    /**
     * The app has exactly one settings row - create it on first access.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function ssoConfigured(): bool
    {
        return filled($this->sso_tenant_id) && filled($this->sso_client_id) && filled($this->sso_client_secret);
    }

    public function blobConfigured(): bool
    {
        return filled($this->blob_account_name) && filled($this->blob_account_key) && filled($this->blob_container);
    }

    /**
     * Whether the credential set attachPreference() will actually use (based
     * on mp_environment) is filled in.
     */
    public function mercadoPagoConfigured(): bool
    {
        return filled($this->activeMercadoPagoAccessToken()) && filled($this->activeMercadoPagoPublicKey());
    }

    public function mercadoPagoIsTestEnvironment(): bool
    {
        return $this->mp_environment !== 'production';
    }

    public function activeMercadoPagoAccessToken(): ?string
    {
        return $this->mercadoPagoIsTestEnvironment() ? $this->mp_test_access_token : $this->mp_access_token;
    }

    public function activeMercadoPagoPublicKey(): ?string
    {
        return $this->mercadoPagoIsTestEnvironment() ? $this->mp_test_public_key : $this->mp_public_key;
    }

    /**
     * Both webhook secrets that could plausibly have signed an incoming
     * notification, keyed by whether that secret means the payment was a
     * test (sandbox) one - a webhook can arrive from either mode regardless
     * of which one is "active" for creating new payment links right now.
     */
    public function mercadoPagoWebhookSecrets(): array
    {
        return array_filter([
            ['secret' => $this->mp_test_webhook_secret, 'is_test' => true],
            ['secret' => $this->mp_webhook_secret, 'is_test' => false],
        ], fn ($entry) => filled($entry['secret']));
    }

    public function mailConfigured(): bool
    {
        return filled($this->mail_host) && filled($this->mail_port) && filled($this->mail_from_address);
    }

    /**
     * Disk config consumed by the 'azure' driver registered in
     * AppServiceProvider::boot() (see Storage::extend('azure', ...)).
     */
    public function toBlobDiskConfig(): array
    {
        return [
            'driver' => 'azure',
            'name' => $this->blob_account_name,
            'key' => $this->blob_account_key,
            'container' => $this->blob_container,
            'endpointSuffix' => $this->blob_endpoint_suffix ?: 'core.windows.net',
        ];
    }

    public function nfseConfigured(): bool
    {
        return filled($this->nfse_cnpj)
            && filled($this->nfse_certificate_path)
            && filled($this->nfse_certificate_password)
            && $this->blobConfigured();
    }

    public function nfseIsProduction(): bool
    {
        return $this->nfse_environment === 'producao';
    }
}
