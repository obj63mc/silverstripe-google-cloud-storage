<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\VisibilityHandler;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\MimeTypeDetector;
use SilverStripe\Assets\Flysystem\ProtectedAdapter as SilverstripeProtectedAdapter;
/**
 * An adapter that allows the use of Google Cloud Storage to store and transmit assets rather than storing them locally.
 */
class ProtectedAdapter extends GoogleStorageAdapter implements SilverstripeProtectedAdapter
{

    /**
     * Pre-signed request expiration time in seconds, or relative string
     *
     * @var int|string
     */
    protected $expiry = 300;


    public function __construct(BucketAdapter $bucketAdapter, $prefix = null, VisibilityHandler $visibilityHandler = null, private string $defaultVisibility = Visibility::PRIVATE, MimeTypeDetector $mimeTypeDetector = null)
    {
        if (!$bucketAdapter) {
            throw new InvalidArgumentException("GC_BUCKET_NAME environment variable not set");
        }
        if (!$prefix) {
            $prefix = 'protected';
        }
        parent::__construct($bucketAdapter->getBucket(), $prefix, $defaultVisibility, $mimeTypeDetector);
    }

    /**
     * @return int|string
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Set expiry. Supports either number of seconds (in int) or
     * a literal relative string.
     *
     * @param int|string $expiry
     * @return $this
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getProtectedUrl($path)
    {
        $dt = new \DateTime();
        if(is_string($this->getExpiry())){
            $dt = $dt->setTimestamp(strtotime($this->getExpiry()));
        } else {
            $dt = $dt->setTimestamp(strtotime('+'.$this->getExpiry().' seconds'));
        }

        return $this->temporaryUrl($path, $dt, new Config());
    }

    public function getVisibility($path)
    {
        // Save an API call
        return ['path' => $path, 'visibility' => self::VISIBILITY_PRIVATE];
    }
}
