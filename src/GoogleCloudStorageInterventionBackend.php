<?php

namespace SilverStripe\GoogleCloudStorage;

use SilverStripe\Assets\InterventionBackend;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\GoogleCloudStorage\Adapter\CachedGoogleCloudStorageAdapter;

/**
 * Reads image dimensions from the metadata stored on the Google Cloud Storage object, rather
 * than downloading the image to measure it.
 */
class GoogleCloudStorageInterventionBackend extends InterventionBackend
{
    protected function getDimensions(): array
    {
        // Seed the dimension cache from the object's metadata, then let the parent carry on as usual.
        // Images without stored dimensions fall through to being loaded and measured.
        $container = $this->getAssetContainer();
        $store = Injector::inst()->get(AssetStore::class);

        if ($container && $container->getHash() && $store instanceof GoogleCloudStorageFlysystemAssetStore) {
            $cache = $this->getCache();
            $key = $this->getDimensionCacheKey($container->getHash(), $container->getVariant());

            if (!$cache->has($key)) {
                $metadata = $store->getObjectMetadata(
                    $container->getFilename(),
                    $container->getHash(),
                    $container->getVariant()
                );
                $width = (int) ($metadata[CachedGoogleCloudStorageAdapter::METADATA_WIDTH] ?? 0);
                $height = (int) ($metadata[CachedGoogleCloudStorageAdapter::METADATA_HEIGHT] ?? 0);

                // The stored size is that of the file. Images which EXIF says are on their
                // side are turned when loaded, if the image manager is set to do so.
                $turned = (int) ($metadata[CachedGoogleCloudStorageAdapter::METADATA_ORIENTATION] ?? 1) >= 5
                    && $this->getImageManager()->driver()->config()->autoOrientation;

                if ($width > 0 && $height > 0) {
                    $cache->set($key, $turned ? [$height, $width] : [$width, $height]);
                }
            }
        }

        return parent::getDimensions();
    }
}
