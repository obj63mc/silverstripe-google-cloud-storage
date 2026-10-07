<?php

namespace SilverStripe\GoogleCloudStorage\Adapter;

use Exception;
use League\Flysystem\CalculateChecksumFromStream;
use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use SilverStripe\Core\Config\Config as SSConfig;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\GoogleCloudStorage\Cache\CacheItemsTrait;

class CachedGoogleCloudStorageAdapter extends GoogleCloudStorageAdapter implements Flushable
{
    use CacheItemsTrait;
    use CalculateChecksumFromStream;
    use Configurable;

    /**
     * Is cache flushing enabled?
     *
     * @config
     * @var boolean
     */
    private static $flush_enabled = true;

    /**
     * @inheritdoc
     */
    public function fileExists(string $path): bool
    {
        $item = $this->getCacheItem($path);

        if ($item && isset($item->extraMetadata()['fileExists'])) {
            return $item->extraMetadata()['fileExists'];
        } else if ($item && isset($item->extraMetadata()['directoryExists'])) {
            return false;
        }

        try {
            $fileExists = parent::fileExists($path);
        } catch (Exception $e) {
            $fileExists = false;
        }

        $state = new FileAttributes(
            path: $path,
            extraMetadata: ['fileExists' => $fileExists]
        );

        // Keep attributes already cached for this file, rather than forcing them to be fetched again
        if ($fileExists && $item) {
            $state = CachedGoogleCloudStorageAdapter::mergeFileAttributes(
                fileAttributesBase: $item,
                fileAttributesExtension: $state,
            );
        }

        $this->saveCacheItem($path, $state);

        return $fileExists;
    }

    /**
     * @inheritdoc
     */
    public function directoryExists(string $path): bool
    {
        $item = $this->getCacheItem($path);

        if ($item && isset($item->extraMetadata()['directoryExists'])) {
            return $item->extraMetadata()['directoryExists'];
        } else if ($item && isset($item->extraMetadata()['fileExists'])) {
            return false;
        }

        try {
            $directoryExists = parent::directoryExists($path);
        } catch (Exception $e) {
            $directoryExists = false;
        }

        $state = new FileAttributes(
            path: $path,
            extraMetadata: ['directoryExists' => $directoryExists]
        );

        $this->saveCacheItem($path, $state);

        return $directoryExists ?? \false;
    }


    public function publicUrl(string $path, Config $config): string
    {
        $item = $this->getCacheItem($path);

        if ($item && !empty($item->extraMetadata()['publicUrl'])) {
            return $item->extraMetadata()['publicUrl'];
        }

        $url = parent::publicUrl($path, $config);

        if ($item) {
            $state = CachedGoogleCloudStorageAdapter::mergeFileAttributes(
                fileAttributesBase: $item,
                fileAttributesExtension: new FileAttributes(
                    path: $path,
                    extraMetadata: ['publicUrl' => $url]
                ),
            );
        } else {
            $state = new FileAttributes(
                path: $path,
                extraMetadata: ['publicUrl' => $url]
            );
        }

        $this->saveCacheItem($path, $state);

        return $url;
    }


    /**
     * @inheritdoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        parent::write($path, $contents, $config);

        $this->purgeCachePath($path);
    }

    /**
     * @inheritdoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        parent::writeStream($path, $contents, $config);

        $this->purgeCachePath($path);
    }

    /**
     * @inheritdoc
     */
    public function read(string $path): string
    {
        try {
            $contents = parent::read($path);
            $item = $this->getCacheItem($path);
        } catch (UnableToReadFile $e) {
            $this->purgeCachePath($path);

            throw $e;
        }

        if (isset($item) && $item instanceof FileAttributes) {
            $fileAttributes = CachedGoogleCloudStorageAdapter::mergeFileAttributes(
                fileAttributesBase: $item,
                fileAttributesExtension: new FileAttributes(
                    path: $path,
                ),
            );
        } else {
            $fileSize = parent::fileSize($path);

            $fileAttributes = new FileAttributes(
                path: $path,
                fileSize: $fileSize ?? 0
            );
        }

        $this->saveCacheItem($path, $fileAttributes);

        return $contents;
    }

    /**
     * @inheritdoc
     */
    public function readStream(string $path)
    {
        try {
            $resource = parent::readStream($path);
        } catch (UnableToReadFile $e) {
            $this->purgeCachePath($path);

            throw $e;
        }

        $item = $this->getCacheItem($path);

        if ($item && $item instanceof FileAttributes) {
            $fileAttributes = CachedGoogleCloudStorageAdapter::mergeFileAttributes(
                fileAttributesBase: $item,
                fileAttributesExtension: new FileAttributes(
                    path: $path,
                ),
            );
        } else {
            $fileAttributes = new FileAttributes(
                path: $path,
            );
        }


        $this->saveCacheItem($path, $fileAttributes);

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): void
    {
        try {
            parent::delete($path);
        } finally {
            $this->purgeCachePath($path);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteDirectory(string $path): void
    {
        try {
            foreach (parent::listContents($path, true) as $storageAttributes) {
                /** @var StorageAttributes $storageAttributes */
                $this->purgeCachePath($storageAttributes->path());
            }

            parent::deleteDirectory($path);
        } finally {
            $this->purgeCachePath($path);
        }
    }

    /**
     * @inheritdoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        parent::createDirectory($path, $config);

        $this->purgeCachePath($path);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        try {
            parent::setVisibility($path, $visibility);
        } catch (UnableToSetVisibility $e) {
            $this->purgeCachePath($path);

            throw $e;
        }

        $attributes = $this->getCacheItem($path);

        if ($attributes) {
            $attributes = CachedGoogleCloudStorageAdapter::mergeFileAttributes(
                fileAttributesBase: $attributes,
                fileAttributesExtension: new FileAttributes(
                    path: $path,
                    visibility: $visibility,
                ),
            );
        } else {
            $attributes = new FileAttributes(
                path: $path,
                visibility: $visibility,
            );
        }

        $this->saveCacheItem($path, $attributes);
    }


    /**
     * @inheritdoc
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->getFileAttributes(
            path: $path,
            loader: function () use ($path) {
                try {
                    return parent::visibility($path);
                } catch (UnableToRetrieveMetadata $e) {
                    return new FileAttributes($path, null, '');
                }
            },
            attributeAccessor: function (FileAttributes $fileAttributes) {
                return $fileAttributes->visibility();
            },
        );
    }

    /**
     * @inheritdoc
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->getFileAttributes(
            path: $path,
            loader: function () use ($path) {
                try {
                    return parent::mimeType($path);
                } catch (UnableToRetrieveMetadata $e) {
                    return new FileAttributes($path, null, null, null, '');
                }
            },
            attributeAccessor: function (FileAttributes $fileAttributes) {
                return $fileAttributes->mimeType();
            },
        );
    }

    /**
     * @inheritdoc
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->getFileAttributes(
            path: $path,
            loader: function () use ($path) {
                try {
                    return parent::lastModified($path);
                } catch (UnableToRetrieveMetadata $e) {
                    return new FileAttributes($path, null, null, time(), null);
                }
            },
            attributeAccessor: function (FileAttributes $fileAttributes) {
                return $fileAttributes->lastModified();
            },
        );
    }

    /**
     * @inheritdoc
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->getFileAttributes(
            path: $path,
            loader: function () use ($path) {
                return parent::fileSize($path);
            },
            attributeAccessor: function (FileAttributes $fileAttributes) {
                return $fileAttributes->fileSize();
            },
        );
    }

    /**
     * @inheritdoc
     */
    public function checksum(string $path, Config $config): string
    {
        $algo = $config->get('checksum_algo', 'md5');
        $metadataKey = 'checksum_' . $algo;
        // Google returns these with an object's other attributes, base64 encoded
        $infoKey = ['md5' => 'md5Hash', 'crc32c' => 'crc32c'][$algo] ?? \null;

        $attributeAccessor = function (StorageAttributes $storageAttributes) use ($metadataKey, $infoKey) {
            $extraMetadata = $storageAttributes->extraMetadata();
            if ($infoKey && isset($extraMetadata[$infoKey])) {
                return bin2hex(base64_decode($extraMetadata[$infoKey]));
            }

            return $extraMetadata[$metadataKey] ?? \null;
        };

        try {
            $fileAttributes = $this->getFileAttributes(
                path: $path,
                loader: function () use ($path, $config, $metadataKey) {
                    // This part is "mirrored" from FileSystem class to provide the fallback mechanism
                    // and be able to cache the result
                    try {
                        $checksum = parent::checksum($path, $config);
                    } catch (ChecksumAlgoIsNotSupported) {
                        $checksum = $this->calculateChecksumFromStream($path, $config);
                    }

                    return new FileAttributes($path, extraMetadata: [$metadataKey => $checksum]);
                },
                attributeAccessor: $attributeAccessor
            );
        } catch (RuntimeException $e) {
            return '';
        }

        return $attributeAccessor($fileAttributes) ?? '';
    }


    /**
     * @inheritdoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->purgeCachePath($source);
        $this->purgeCachePath($destination);

        try {
            parent::move($source, $destination, $config);
        } catch (UnableToMoveFile $e) {
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->purgeCachePath($source);
        $this->purgeCachePath($destination);

        try {
            parent::copy($source, $destination, $config);
        } catch (UnableToCopyFile $e) {
            throw $e;
        }
    }


    public static function flush()
    {
        if (SSConfig::inst()->get(static::class, 'flush_enabled')) {
            Injector::inst()->get(CacheInterface::class . '.gcsCache')->clear();
        }
    }
}
