<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;

class PublicAdapter extends GoogleStorageAdapter implements SilverstripePublicAdapter
{
    public function __construct(BucketAdapter $bucketAdapter, $prefix = null, $storageApiUri = null)
    {
        if (!$bucketAdapter) {
            throw new InvalidArgumentException("GC_BUCKET_NAME environment variable not set");
        }
        if (!$prefix) {
            $prefix = 'public';
        }
        parent::__construct($bucketAdapter->getClient(), $bucketAdapter->getBucket(), $prefix, $storageApiUri);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPublicUrl($path)
    {
        return $this->getUrl($path);
    }
}
