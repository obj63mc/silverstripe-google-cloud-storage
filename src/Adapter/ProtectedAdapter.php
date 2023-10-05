<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAdapter as SilverstripeProtectedAdapter;

class ProtectedAdapter extends GoogleCloudStorageAdapter implements SilverstripeProtectedAdapter
{
    private BucketAdapter $bucketAdapter;

    public function __construct(BucketAdapter $bucketAdapter, string $prefix = null)
    {
        if (!$prefix) {
            $prefix = 'protected';
        }

        $this->bucketAdapter = $bucketAdapter;

        parent::__construct($bucketAdapter->getBucket(), $prefix);
    }


    /**
     * @param string $path
     *
     * @return string
     */
    public function getProtectedUrl($path): string
    {
        $object = $this->bucketAdapter->getBucket()->object($path);
        return $object->signedUrl(date_create('+1 day'));
    }

    public function getVisibility($path)
    {
        // Save an API call
        return ['path' => $path, 'visibility' => self::VISIBILITY_PRIVATE];
    }
}
