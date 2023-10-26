<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\VisibilityHandler;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\MimeTypeDetector;
use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;

class PublicAdapter extends GoogleStorageAdapter implements SilverstripePublicAdapter
{
    public function __construct(BucketAdapter $bucketAdapter, $prefix = null, VisibilityHandler $visibilityHandler = null, private string $defaultVisibility = Visibility::PUBLIC, MimeTypeDetector $mimeTypeDetector = null)
    {
        if (!$bucketAdapter) {
            throw new InvalidArgumentException("GC_BUCKET_NAME environment variable not set");
        }
        if (!$prefix) {
            $prefix = 'public';
        }
        parent::__construct($bucketAdapter->getBucket(), $prefix, $defaultVisibility, $mimeTypeDetector);
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
