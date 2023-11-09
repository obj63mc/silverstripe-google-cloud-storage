<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;
use SilverStripe\Control\Controller;

/**
 * Class PublicCDNAdapter
 * @package SilverStripe\S3\Adapter
 */
class PublicCDNAdapter extends PublicAdapter implements SilverstripePublicAdapter
{
    protected ?string $cdnPrefix;

    public function __construct(BucketAdapter $bucketAdapter, string $prefix = null, string $cdnPrefix = null)
    {
        $this->cdnPrefix = $cdnPrefix;
        parent::__construct($bucketAdapter, $prefix);
    }

    public function getPublicUrl($path): string
    {
        return Controller::join_links($this->cdnPrefix, $path);
    }
}
