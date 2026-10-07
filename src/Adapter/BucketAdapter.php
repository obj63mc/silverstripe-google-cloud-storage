<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use InvalidArgumentException;

/**
 * Holds the Google Cloud Storage client and bucket shared by the public and protected adapters.
 */
class BucketAdapter
{
    protected $bucket;

    protected $client;

    /**
     * @param string $bucketName
     * @param string $keyFile JSON of the service account key
     */
    public function __construct($bucketName, $keyFile)
    {
        if (!$bucketName) {
            throw new InvalidArgumentException("GC_BUCKET_NAME environment variable not set");
        }

        $this->client = new StorageClient([
            'keyFile' => json_decode($keyFile, true)
        ]);

        $this->bucket = $this->client->bucket($bucketName);
    }

    public function getBucket(): Bucket
    {
        return $this->bucket;
    }

    public function getClient(): StorageClient
    {
        return $this->client;
    }
}
