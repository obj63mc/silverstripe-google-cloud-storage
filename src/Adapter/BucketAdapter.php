<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Google\Cloud\Storage\StorageClient;

class BucketAdapter {
    protected $bucket;
    protected $client;
    public function __construct($bucketname, $keyFile){
        $this->client = new StorageClient([
            'keyFile'=> json_decode($keyFile, true),
        ]);


        $this->bucket = $this->client->bucket($bucketname);
    }

    public function getBucket(){
        return $this->bucket;
    }

    public function getClient(){
        return $this->client;
    }
}
