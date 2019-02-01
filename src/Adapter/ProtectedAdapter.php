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
        $object = $this->getObject($path);
        $signedUrl = $object->signedUrl(date_create('+1 day'), []);
        if ($this->getStorageApiUri() !== self::STORAGE_API_URI_DEFAULT) {
            if (!isset($options['cname'])) {
                list($url, $params) = explode('?', $signedUrl, 2);
                $signedUrl = $this->getUrl($path) . '?' . $params;
            }
        }

        return $signedUrl;
    }
}
