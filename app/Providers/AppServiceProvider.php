<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Filesystem\AzureFilesystemAdapter;
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, [MicrosoftExtendSocialite::class, 'handle']);

        $this->registerAzureDisk();
        $this->registerMailConfig();
    }

    /**
     * Registers the 'azure' Storage driver and, when configured (via the
     * Configurações → Armazenamento admin page, never via .env), points the
     * default disk and Spatie MediaLibrary's disk at it - uploads are never
     * written to the local filesystem (see Setting::blobConfigured() guards
     * in UserController/UserRepository).
     */
    private function registerAzureDisk(): void
    {
        Storage::extend('azure', function ($app, $config) {
            $connectionString = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=%s',
                $config['name'],
                $config['key'],
                $config['endpointSuffix'] ?? 'core.windows.net',
            );

            $containerClient = BlobServiceClient::fromConnectionString($connectionString)
                ->getContainerClient($config['container']);

            $adapter = new AzureBlobStorageAdapter($containerClient);

            return new AzureFilesystemAdapter($adapter, new Filesystem($adapter, $config), $config);
        });

        try {
            $setting = Setting::current();
        } catch (\Throwable $e) {
            // settings table doesn't exist yet (fresh install before migrate) - nothing to wire up.
            return;
        }

        if ($setting->blobConfigured()) {
            config([
                'filesystems.disks.azure' => $setting->toBlobDiskConfig(),
                'filesystems.default' => 'azure',
                'media-library.disk_name' => 'azure',
            ]);
        }
    }

    /**
     * SMTP credentials come from Configurações → E-mail (DB-backed, never
     * .env), same runtime-override pattern as the Azure disk above.
     */
    private function registerMailConfig(): void
    {
        try {
            $setting = Setting::current();
        } catch (\Throwable $e) {
            return;
        }

        if (! $setting->mailConfigured()) {
            return;
        }

        config([
            'mail.mailers.smtp.host' => $setting->mail_host,
            'mail.mailers.smtp.port' => $setting->mail_port,
            'mail.mailers.smtp.username' => $setting->mail_username,
            'mail.mailers.smtp.password' => $setting->mail_password,
            'mail.mailers.smtp.encryption' => $setting->mail_encryption ?: null,
            'mail.from.address' => $setting->mail_from_address,
            'mail.from.name' => $setting->mail_from_name ?: config('app.name'),
        ]);
    }
}
