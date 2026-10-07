<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use League\Flysystem\GoogleCloudStorage\VisibilityHandler;
use League\MimeTypeDetection\MimeTypeDetector;
use SilverStripe\Assets\Flysystem\PublicAdapter as SilverstripePublicAdapter;
use SilverStripe\Control\Controller;

use const ASSETS_DIR;

/**
 * Class PublicCDNAdapter
 * @package SilverStripe\GoogleCloudStorage\Adapter
 */
class PublicCDNAdapter extends PublicAdapter implements SilverstripePublicAdapter
{
    protected $cdnPrefix;

    protected $cdnAssetsDir;

    public function __construct(
        BucketAdapter $bucketAdapter,
        $prefix = '',
        ?VisibilityHandler $visibility = null,
        ?MimeTypeDetector $mimeTypeDetector = null,
        $cdnPrefix = '',
        $cdnAssetsDir = ''
    ) {
        $this->cdnPrefix = $cdnPrefix;
        $this->cdnAssetsDir = $cdnAssetsDir ? $cdnAssetsDir : ASSETS_DIR;
        parent::__construct($bucketAdapter, $prefix, $visibility, $mimeTypeDetector);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPublicUrl($path)
    {
        return Controller::join_links($this->cdnPrefix, $this->cdnAssetsDir, $path);
    }


    public function setCdnPrefix(string $cdnPrefix): self
    {
        $this->cdnPrefix = $cdnPrefix;

        return $this;
    }
}
