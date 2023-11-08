<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use League\Flysystem\Config;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAdapter as SilverstripeProtectedAdapter;

class ProtectedAdapter extends GoogleCloudStorageAdapter implements SilverstripeProtectedAdapter
{
    public function __construct(BucketAdapter $bucketAdapter, string $prefix = null)
    {
        if (!$prefix) {
            $prefix = 'protected';
        }

        parent::__construct($bucketAdapter->getBucket(), $prefix);
    }


    /**
     * @param string $path
     *
     * @return string
     */
    public function getProtectedUrl($path): string
    {
        return $this->temporaryUrl($path, new \DateTime('+1 day'), new Config());
    }
}
