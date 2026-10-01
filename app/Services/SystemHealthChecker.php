<?php

namespace App\Services;

use App\Models\ServiceStatus;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Refreshes the "Status do Sistema" rows shown on the dashboard - see
 * App\Console\Commands\CheckSystemHealth (scheduled every 5 minutes).
 * NFS-e's two government endpoints are actually pinged (that's the one
 * genuinely flaky dependency this app has hit in practice); everything
 * else here only reflects whether it's configured, matching the manual
 * "Testar" buttons already on the Configurações screen for those.
 */
class SystemHealthChecker
{
    public function checkAll(): void
    {
        $setting = Setting::current();

        $this->checkNfse($setting);

        $this->record('mercadopago', 'Mercado Pago', $setting->mercadoPagoConfigured());
        $this->record('mail', 'E-mail (SMTP)', $setting->mailConfigured());
        $this->record('storage', 'Armazenamento (Azure Blob)', $setting->blobConfigured());
        $this->record('sso', 'Login Microsoft (SSO)', $setting->ssoConfigured());
    }

    private function checkNfse(Setting $setting): void
    {
        if (! $setting->nfseConfigured()) {
            $this->record('nfse_sefin', 'Nota Fiscal - Emissão (SEFIN)', false);
            $this->record('nfse_adn', 'Nota Fiscal - PDF (ADN)', false);

            return;
        }

        try {
            $result = app(NFSeService::class)->checkHealth($setting);
        } catch (\Throwable $e) {
            Log::warning("SystemHealthChecker: falha ao checar NFS-e: {$e->getMessage()}");

            ServiceStatus::updateOrCreate(['key' => 'nfse_sefin'], [
                'label' => 'Nota Fiscal - Emissão (SEFIN)',
                'status' => ServiceStatus::STATUS_DOWN,
                'message' => $e->getMessage(),
                'checked_at' => now(),
            ]);
            ServiceStatus::updateOrCreate(['key' => 'nfse_adn'], [
                'label' => 'Nota Fiscal - PDF (ADN)',
                'status' => ServiceStatus::STATUS_DOWN,
                'message' => $e->getMessage(),
                'checked_at' => now(),
            ]);

            return;
        }

        ServiceStatus::updateOrCreate(['key' => 'nfse_sefin'], [
            'label' => 'Nota Fiscal - Emissão (SEFIN)',
            'status' => $result['sefin']['ok'] ? ServiceStatus::STATUS_OK : ServiceStatus::STATUS_DOWN,
            'message' => $result['sefin']['message'],
            'checked_at' => now(),
        ]);

        ServiceStatus::updateOrCreate(['key' => 'nfse_adn'], [
            'label' => 'Nota Fiscal - PDF (ADN)',
            'status' => $result['adn']['ok'] ? ServiceStatus::STATUS_OK : ServiceStatus::STATUS_DOWN,
            'message' => $result['adn']['message'],
            'checked_at' => now(),
        ]);
    }

    private function record(string $key, string $label, bool $configured): void
    {
        ServiceStatus::updateOrCreate(['key' => $key], [
            'label' => $label,
            'status' => $configured ? ServiceStatus::STATUS_OK : ServiceStatus::STATUS_UNCONFIGURED,
            'message' => null,
            'checked_at' => now(),
        ]);
    }
}
