<?php

namespace App\Foundation\FileManager;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use App\Foundation\Media\MediaType;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\ValidationException;
use JsonException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class FileAdder
{
    use Macroable;

    protected ?Model $subject = null;

    protected bool $preserveOriginal = false;

    /** @var SymfonyUploadedFile|string */
    protected $file;

    protected ?User $user;

    protected array $properties = [];

    protected ?array $customProperties = [];

    protected ?array $manipulations = [];

    protected string $pathToFile = '';

    protected string $path = '';

    protected string $fileName = '';

    protected string $mediaName = '';

    protected null|string $diskName = '';

    protected string $conversionsDiskName = '';

    protected ?Closure $fileNameSanitizer;

    protected bool $generateResponsiveImages = false;

    protected array $customHeaders = [];

    protected int $fileSize = 0;

    public ?int $order = null;

    public ?string $collection_name = null;

    public ?FileManager $fileManager = null;

    public function __construct(
        protected ?Filesystem      $filesystem,
        protected ConfigRepository $configRepository
    )
    {
        $this->fileNameSanitizer = fn($fileName) => $this->defaultSanitizer($fileName);
    }

    /**
     * @param Model $subject
     * @return $this
     */
    public function setSubject(Model $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /*
     * Set the file that needs to be imported.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->user = auth()->user();

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->fileSize = 0;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);
            return $this;
        }

//        if ($file instanceof RemoteFile) {
//            $this->pathToFile = $file->getKey();
//            $this->setFileName($file->getFilename());
//            $this->mediaName = $file->getName();
//
//            return $this;
//        }
        if ($file instanceof UploadedFile || $file instanceof SymfonyUploadedFile) {

            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $this->fileSize = $file->getSize();
            return $this;
        }

//        if ($file instanceof SymfonyFile) {
//            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
//            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
//            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
//
//            return $this;
//        }

//        if ($file instanceof TemporaryUpload) {
//            return $this;
//        }

//        throw UnknownType::create();
    }

    /**
     * @param string|null $path
     * @return $this
     */
    public function setPath(
        null|string $path = '/',
    ): static
    {
        $this->path = $path??'';
        return $this;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @param string|null $disk
     * @return FileAdder
     */
    public function setDisk(
        null|string $disk,
    ): self
    {
        $this->diskName = $disk;
        return $this;
    }

    /**
     * @param string $collectionName
     * @param string $diskName
     * @return Model|void|null
     * @throws ValidationException
     */
    public function toMediaCollection(string $collectionName = 'default', string $diskName = '')
    {
//        $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
        $sanitizedFileName = $this->fileName;
        $fileName = $this->originalFileName($sanitizedFileName);
        $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));
        $disk = $this->determineDiskName($diskName, $collectionName);

        $path = Storage::disk($disk)->path(request()->tenant->uuid . '/' . $this->pathToFile);

        $onlyPath = Str::before($this->pathToFile, '/') === $this->pathToFile ?
            '/' :
            Str::before($this->pathToFile, '/');

        $onlyPath = $this->path? $onlyPath.$this->path: $onlyPath;

        $this->ensureDiskExists($disk);
        $this->collection_name = $this->determineCollection($collectionName);
        if ($this->file instanceof UploadedFile || $this->file instanceof SymfonyUploadedFile) {
            if (!Storage::disk($disk)
                ->exists(request()->tenant->uuid .$this->path . '/'.  $this->file->getClientOriginalName())) {
                $path = Str::replace('//', '/', request()->tenant->uuid .'/' .$this->path . '/');
                if (Storage::disk($disk)->putFileAs(
                    $path,
                    $this->file,
                    $this->file->getClientOriginalName()
                )) {
                    $media = new FileManager();
                    $media->user_id = $this->user?->id;
                    $media->name = $this->fileName;
                    $media->disk = $disk;
                    $media->collection = $this->determineCollection($collectionName);
                    $media->type = $this->file->getClientMimeType();
                    $media->size = $this->fileSize;
                    $media->model_type = $this->subject::class;
                    $media->model_id = $this->subject->id;
                    $media->group = MediaType::getGroupType($media->type);
                    $media->path = $onlyPath ?? '/';
                    $media->ext = \File::extension($this->fileName);
                    return $this->attachMedia($media);
                }

                return $this->subject;

            }
            return $this->subject;
//            $media = new FileManager();
//            $media->user_id = $this->user?->id;
//            $media->name = $this->fileName;
//            $media->disk = $disk;
//            $media->collection = $this->determineCollection($collectionName);
//            $media->type = $this->file->getClientMimeType();
//            $media->size = 0;
//            $media->group = MediaType::getGroupType($media->type);
//            $media->path = $onlyPath??'/';
//            $media->ext = \File::extension($this->fileName);
//            return $this->attachMedia($media);


        }
////
//        if ($this->file instanceof TemporaryUpload) {
//            return $this->toMediaCollectionFromTemporaryUpload($collectionName, $diskName, $this->fileName);
//        }
//


        if (!is_string($path)) {
            throw ValidationException::withMessages(['file' => "{$this->pathToFile} was not found"]);
        }
//        if (filesize($this->pathToFile) > config('media-library.max_file_size')) {
//            throw FileIsTooBig::create($this->pathToFile);
//        }
//
//        $mediaClass = config('media-library.media_model');
//        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */


        $this->ensureMediaExists([
            'name' => $this->fileName,
            'disk' => $disk,
            'path' => $onlyPath === '/' ? '' : $onlyPath
        ]);

//        $media = $this->subject->media()->create([
//            'user_id' => auth()->user()->id,
//            'file_manager_id' => $this->fileManager?->id,
//            'collection' => $this->determineCollection($collectionName),
//            'size' => $this->fileManager->size,
//        ]);

        return $this->attachMedia($this->fileManager);
    }


    /**
     * @param string $fileName
     * @return string
     */
    public function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function originalFileName(string $fileName): string
    {
        $extLength = strlen(pathinfo($fileName, PATHINFO_EXTENSION));
        return substr($fileName, 0, strlen($fileName) - ($extLength ? $extLength + 1 : 0));
    }

    /**
     * @param string      $file
     * @param string|null $extension
     * @return string
     */
    protected function appendExtension(string $file, ?string $extension): string
    {
        return $extension
            ? $file . '.' . $extension
            : $file;
    }

    /**
     * @param string $diskName
     * @param string $collectionName
     * @return string
     */
    protected function determineDiskName(string $diskName, string $collectionName): string
    {
        if ($diskName !== '') {
            return $diskName;
        }

        if ($collection = $this->getMediaCollection($collectionName)) {
            $collectionDiskName = $collection->diskName;
            if ($collectionDiskName !== '') {
                return $collectionDiskName;
            }
        }


        return 'tenancy';
    }

    /**
     * @param string $collectionName
     * @return string|null
     */
    protected function determineCollection(string $collectionName): ?string
    {
        if ($collection = $this->getMediaCollection($collectionName)) {
            $collectionName = $collection->name;
            if ($collectionName !== '') {
                return $collectionName;
            }
        }

        return null;
    }

    /**
     * @param string $collectionName
     * @return MediaCollection|null
     */
    protected function getMediaCollection(string $collectionName): ?MediaCollection
    {
        $this->subject->registerMediaCollections();

        $disk = $this->diskName;
        return collect($this->subject->mediaCollections)
            ->first(function (MediaCollection $collection) use ($collectionName, $disk) {
                $disk? $collection->useDisk($disk): optional($collection)->diskName;
                $this->diskName = $disk;
                $this->manipulations = optional($collection)->mediaConversion;
                $this->customProperties = optional($collection)->customProperties;
                return $collection->name !== $collectionName;
            });
    }

    /**
     * @param string $diskName
     * @throws ValidationException
     */
    protected function ensureDiskExists(string $diskName): void
    {
        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw ValidationException::withMessages(['disk' => "{$diskName} was not found"]);
        }
    }

    /**
     * @param array $needles
     * @throws ValidationException
     */
    protected function ensureMediaExists(array $needles): void
    {
        if (count($needles) > 0) {
            $this->fileManager = FileManager::where($needles)->first();

            if (!$this->fileManager) {
                $name = optional($needles)['name'];
                $disk = optional($needles)['disk'];
                throw ValidationException::withMessages([
                    'media' => "We couldn't find the media {$name}, in the selected disk {$disk}."
                ]);
            }
        }
    }

    /**
     * @param FileManager $media
     * @return Model|void|null
     * @throws JsonException
     */
    protected function attachMedia(FileManager $media)
    {
        if (!$this->subject->exists) {
            $this->subject->prepareToAttachMedia($media, $this);

            $class = $this->subject::class;

            $class::created(function ($model) {
                $model->processUnattachedMedia(function (FileManager $media, self $fileAdder) use ($model) {
                    $this->processMediaItem($model, $media, $fileAdder);
                });
            });
            return;
        }


        if (!$this->subject->media()->where('media.id', $media->id)->exists()) {
            $this->subject->media()->save($media, [
                'user_id' => $this->user?->id,
                'uuid' => Str::uuid(),
                'collection' => $this->collection_name,
                'size' => $media->size,
                'manipulations' => $this->manipulations ? json_encode($this->manipulations, JSON_THROW_ON_ERROR) : NULL,
                'custom_properties' => $this->customProperties ? json_encode($this->customProperties, JSON_THROW_ON_ERROR) : NULL
            ]);
        }
        return $this->subject;
    }

}
