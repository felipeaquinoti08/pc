<?php

namespace App\Support\Filesystem;

use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;

/**
 * Laravel's FilesystemAdapter (this app runs on laravel/framework 12.64) only
 * calls an adapter's getUrl()/getTemporaryUrl() methods - it doesn't yet know
 * about League Flysystem's newer PublicUrlGenerator/TemporaryUrlGenerator
 * interfaces, which is what azure-oss/storage-blob-flysystem's adapter
 * implements instead (publicUrl()/temporaryUrl()). Without this bridge,
 * Storage::disk('azure')->url(...) throws "This driver does not support
 * retrieving URLs." Since the container is private (isPublicContainer
 * defaults to false on the inner adapter), publicUrl() itself already
 * generates a long-lived SAS URL - exactly the temporary token needed to
 * read a private blob.
 */
class AzureFilesystemAdapter extends FilesystemAdapter
{
    public function __construct(
        private readonly AzureBlobStorageAdapter $azureAdapter,
        FilesystemOperator $driver,
        array $config = [],
    ) {
        parent::__construct($driver, $azureAdapter, $config);
    }

    public function url($path)
    {
        return $this->azureAdapter->publicUrl($path, new Config);
    }

    public function temporaryUrl($path, $expiration, array $options = [])
    {
        return $this->azureAdapter->temporaryUrl($path, $expiration, new Config($options));
    }
}
