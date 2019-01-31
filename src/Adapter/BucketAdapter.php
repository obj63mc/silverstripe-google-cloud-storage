<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Google\Cloud\Storage\StorageClient;

class BucketAdapter {
    protected $bucket;
    protected $client;
    public function __construct($bucketname, $projectId, $keyFilePath){
        $this->client = new StorageClient([
            'projectId'=>$projectId,
            'keyFilePath' => BASE_PATH.'/'.$keyFilePath
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
