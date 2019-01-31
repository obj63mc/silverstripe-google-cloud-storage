<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAdapter as SilverstripeProtectedAdapter;

/**
 * An adapter that allows the use of Google Cloud Storage to store and transmit assets rather than storing them locally.
 */
class ProtectedAdapter extends GoogleStorageAdapter implements SilverstripeProtectedAdapter
{


    public function __construct(BucketAdapter $bucketAdapter, $prefix = null, $storageApiUri = null)
    {
        if (!$bucketAdapter) {
            throw new InvalidArgumentException("GC_BUCKET_NAME environment variable not set");
        }
        if (!$prefix) {
            $prefix = 'protected';
        }
        parent::__construct($bucketAdapter->getClient(), $bucketAdapter->getBucket(), $prefix, $storageApiUri);
    }


    /**
     * @param string $path
     *
     * @return string
     */
    public function getProtectedUrl($path)
    {
        return $this->getUrl($path);
    }
}
