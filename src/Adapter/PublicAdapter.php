<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use League\Flysystem\Config;
use League\Flysystem\GoogleCloudStorage\VisibilityHandler;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\MimeTypeDetector;
use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;

class PublicAdapter extends CachedGoogleCloudStorageAdapter implements SilverstripePublicAdapter
{

    public function __construct(BucketAdapter $bucketAdapter, $prefix = '', ?VisibilityHandler $visibility = null, ?MimeTypeDetector $mimeTypeDetector = null)
    {
        if (!$prefix) {
            $prefix = 'public';
        }

        parent::__construct($bucketAdapter->getBucket(), $prefix, $visibility, Visibility::PUBLIC, $mimeTypeDetector);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPublicUrl($path)
    {

        return $this->publicUrl($path, new Config());
    }
}
