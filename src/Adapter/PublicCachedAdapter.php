<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use jgivoni\Flysystem\Cache\CacheAdapter;
use SilverStripe\Assets\Flysystem\PublicAdapter;

class PublicCachedAdapter extends CacheAdapter implements PublicAdapter
{
    public function getPublicUrl($path)
    {
        return $this->adapter->getPublicUrl($path);
    }
}
