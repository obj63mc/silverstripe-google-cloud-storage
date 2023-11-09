<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use League\Flysystem\Config;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;

class PublicAdapter extends GoogleCloudStorageAdapter implements SilverstripePublicAdapter
{
    public function __construct(BucketAdapter $bucketAdapter, string $prefix = null)
    {
        if (!$prefix) {
            $prefix = 'public';
        }

        parent::__construct($bucketAdapter->getBucket(), $prefix);
    }

    public function getPublicUrl($path): string
    {
        return $this->publicUrl($path, new Config());
    }
}
