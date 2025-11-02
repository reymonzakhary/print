<?php

namespace App\Foundation\FileManager;

use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class MediaCollection
{
    use Macroable;

    /** @var array */
    public array $mediaConversion;

    /** @var array */
    public array $customProperties;

    /** @var string */
    public string $diskName = '';

    /** @var string */
    public string $conversionsDiskName = '';

    /** @var callable */
    public $mediaConversionRegistrations;

    /** @var bool */
    public bool $generateResponsiveImages = false;

    /** @var callable */
    public $acceptsFile;

    /** @var array */
    public array $acceptsMimeTypes = [];

    /** @var bool|int */
    public int|bool $collectionSizeLimit = false;

    /** @var bool */
    public bool $singleFile = false;

    /** @var string */
    public string $fallbackUrl = '';

    public string $fallbackPath = '';

    public function __construct(
        public string $name
    )
    {
        $this->mediaConversionRegistrations = function () {
        };

        $this->acceptsFile = fn() => true;
    }

    public static function create($name)
    {
        return new static($name);
    }

    public function useDisk(string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    public function storeConversionsOnDisk(string $conversionsDiskName): self
    {
        $this->conversionsDiskName = $conversionsDiskName;

        return $this;
    }

    public function acceptsFile(callable $acceptsFile): self
    {
        $this->acceptsFile = $acceptsFile;

        return $this;
    }

    public function acceptsMimeTypes(array $mimeTypes): self
    {
        $this->acceptsMimeTypes = $mimeTypes;

        return $this;
    }

    public function singleFile(): self
    {
        return $this->onlyKeepLatest(1);
    }

    public function onlyKeepLatest(int $maximumNumberOfItemsInCollection): self
    {
        if ($maximumNumberOfItemsInCollection < 1) {
            throw new InvalidArgumentException("You should pass a value higher than 0. `{$maximumNumberOfItemsInCollection}` given.");
        }

        $this->singleFile = ($maximumNumberOfItemsInCollection === 1);

        $this->collectionSizeLimit = $maximumNumberOfItemsInCollection;

        return $this;
    }

    public function registerMediaConversions(callable $mediaConversionRegistrations)
    {
        $this->mediaConversionRegistrations = $mediaConversionRegistrations;
    }

    public function useFallbackUrl(string $url): self
    {
        $this->fallbackUrl = $url;

        return $this;
    }

    /**
     * @param array $mediaConversion
     * @return $this
     */
    public function setConversions(array $mediaConversion): self
    {
        $this->mediaConversion = $mediaConversion;
        return $this;
    }

    /**
     * @param array $customProperties
     * @return $this
     */
    public function useCustomProperties(array $customProperties): self
    {
        $this->customProperties = $customProperties;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function useFallbackPath(string $path): self
    {
        $this->fallbackPath = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function withResponsiveImages(): self
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function withResponsiveImagesIf($condition): self
    {
        $this->generateResponsiveImages = (bool)(is_callable($condition) ? $condition() : $condition);

        return $this;
    }
}
