<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use jgivoni\Flysystem\Cache\CacheAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAdapter;

class ProtectedCachedAdapter extends CacheAdapter implements ProtectedAdapter
{
    public function getProtectedUrl($path)
    {
        return $this->adapter->getProtectedUrl($path);
    }
}
