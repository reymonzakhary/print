<?php

namespace App\Foundation\FileManager\Traits;

use App\Foundation\FileManager\Contracts\FileManagerInterface;
use App\Foundation\FileManager\MediaCollection;
use App\Foundation\Media\FileManager as MediaFileManager;
use App\Models\Tenant\Box;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartVariation;
use App\Models\Tenant\Category;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Media\FileManager as FileManagerModel;
use App\Models\Tenant\Option;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sku;
use App\Models\Tenant\User;
use App\Models\Tenant\Variation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;

trait InteractWithMedia
{
    public array $mediaCollections = [];

    /**
     * @return MorphToMany
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(FileManager::class, 'model',
            'media')->withPivot(['user_id', 'uuid', 'collection', 'size', 'manipulations', 'custom_properties'])
            ->withTimestamps();
    }

    /**
     * @return Collection
     */
    public function getAllMedia(): Collection
    {
        return $this->media()->get();
    }

    /**
     * @return object|null
     */
    public function getSingleMediaAttribute(): ?object
    {
        return $this->mediaGetOne();
    }

    /**
     * @return object|null
     */
    public function mediaGetOne(): ?object
    {
        return $this->media()?->first();
    }

    /**
     * @return string
     */
    public function getLocKeyName(): string
    {
        return 'row_id';//$this->getKeyName();
    }


    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file
     * @param string|null $path
     * @param string|null $disk
     * @return mixed
     */
    public function addMedia(
        string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file,
        null|string $path =null,
        null|string $disk = null,
    ): mixed
    {
        if (is_array($file)) {
            return $this->addMedias($file,$path, $disk);
        }
        return app(FileManagerInterface::class)->create($this, $file,$path, $disk);
    }

    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file
     * @return Brand|InteractWithMedia|Box|Cart|CartVariation|Category|Option|Product|Sku|Variation
     */
    public function dropAndBuilt(string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file): self
    {
        app(FileManagerInterface::class)->drop($this);
        if (is_array($file)) {
            return $this->addMedias($file);
        }
        return app(FileManagerInterface::class)->create($this, $file);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function addMediaFromRequest(string $key): mixed
    {
        return app(FileManagerInterface::class)->createFromRequest($this, $key);
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
    }

    /**
     * @param string $collectionName
     * @return MediaCollection|null
     */
    public function getMediaCollection(string $collectionName = 'default'): ?MediaCollection
    {
        $this->registerMediaCollections();

        return collect($this->mediaCollections)
            ->first(fn(MediaCollection $collection) => $collection->name === $collectionName);
    }

    /**
     * @param string $name
     * @return MediaCollection
     */
    public function addMediaCollection(string $name): MediaCollection
    {
        $mediaCollection = MediaCollection::create($name);

        $this->mediaCollections[] = $mediaCollection;

        return $mediaCollection;
    }

    /**
     *
     */
    public function scopeDetachMedia(): void
    {
        $this->media()->detach();
    }

    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file
     * @param string|null $path
     * @param string|null $disk
     * @return Brand|InteractWithMedia|Box|Cart|CartVariation|Category|Option|Product|Sku|Variation
     */
    public function addMedias(
        string|\Symfony\Component\HttpFoundation\File\UploadedFile|array $file,
        null|string $path =null,
        null|string $disk = null
    ): self
    {
        collect($file)
            ->map(function ($key) use ($disk, $path) {
                if ($key instanceof UploadedFile) {
                    app(FileManagerInterface::class)
                        ->create($this, $key,  $path,$disk);
                } else {
                    $search = ['[', ']', '"', "'"];
                    $replace = ['.', '', '', ''];
                    $key = str_replace($search, $replace, $key);
                    app(FileManagerInterface::class)
                        ->create($this, $key,$path, $disk);
                }
            });
        return $this;
    }

    public function getMedia(
        ?string $collection = null,
        string  $disk = "tenancy"
    )
    {
        $where = [
            ['model_type', get_class($this)],
            ['model_id', $this->id],
            ['disk', $disk]
        ];
        if ($collection) {
            $where[] = ['collection', $collection];
        }
        return FileManagerModel::where($where)->get();
    }

    public function addMediaFromDisk(
        ?User  $user,
        object $fileInfo,
        string $disk = 'tenancy',
        string $path = '/',
        string $collection = null
    )
    {
        $fileManager = app(MediaFileManager::class);
        $fileInfo->collection = $collection ?? $fileInfo->collection;
        $fileManager->StoreFileInDataBase($user->id, $disk, $path, $fileInfo, get_class($this), $this->id);

    }

    /**
     * Boot the trait
     * @return void
     */
    protected static function bootInteractWithMedia()
    {
    }

    /**
     * Initialize the trait
     *
     * @return void
     */
    protected function initializeInteractWithMedia()
    {
    }
}
