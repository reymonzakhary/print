<?php

namespace App\Foundation\Media;

use Alexusmai\LaravelFileManager\Events\Deleted;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Alexusmai\LaravelFileManager\Traits\CheckTrait;
use Alexusmai\LaravelFileManager\Traits\PathTrait;
use App\Enums\FileExtensions;
use App\Foundation\Media\Traits\ContentTrait;
use App\Foundation\Media\Traits\FmDeleteTrait;
use App\Foundation\Media\Traits\FmRenameTrait;
use App\Foundation\Media\TransferService\TransferFactory;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Models\Tenants\User;
use App\Models\User as SystemUser;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Interfaces\ImageManagerInterface;
use League\Flysystem\FilesystemException;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class FileManager
{
    use CheckTrait;
    use ContentTrait;
    use PathTrait;
    use FmDeleteTrait;
    use FmRenameTrait;

    /**
     * FileManager constructor.
     *
     * @param ConfigRepository $configRepository
     * @param ImageManagerInterface $imageManager
     * @param LoggerInterface $logger
     * @param NullLogger $nullLogger
     */
    public function __construct(
        private ConfigRepository $configRepository,
        private ImageManagerInterface $imageManager,
        private LoggerInterface $logger,
        private NullLogger $nullLogger
    ) {}

    /**
     * Initialize App
     *
     * @return array
     */
    final public function initialize(): array
    {
        // if config not found
        if (!config()->has('file-manager')) {
            return [
                'result' => [
                    'status' => 'danger',
                    'message' => 'noConfig'
                ],
            ];
        }

        $config = [
            'acl' => $this->configRepository->getAcl(),
            'leftDisk' => $this->configRepository->getLeftDisk(),
            'rightDisk' => $this->configRepository->getRightDisk(),
            'leftPath' => $this->configRepository->getLeftPath(),
            'rightPath' => $this->configRepository->getRightPath(),
            'windowsConfig' => $this->configRepository->getWindowsConfig(),
            'hiddenFiles' => $this->configRepository->getHiddenFiles(),
        ];

        // disk list
        foreach ($this->configRepository->getDiskList() as $disk) {
            if (array_key_exists($disk, config('filesystems.disks'))) {
                $config['disks'][$disk] = Arr::only(
                    config('filesystems.disks')[$disk], ['driver']
                );
            }
        }

        // get language
        $config['lang'] = app()->getLocale();

        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'config' => $config,
        ];
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     */
    final public function content(
        string $disk,
        string $path
    ): array
    {
        // get content for the selected directory
        $content = $this->getContent($disk, $path);
        // files transform
        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files' => $content['files'],
        ];
    }

    /**
     * Get part of the directory tree
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     * @throws FilesystemException
     */
    final public function tree(
        string $disk,
        string $path
    ): array
    {
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }

    /**
     * Upload files
     *
     * @param User|SystemUser|null $user
     * @param string|null          $disk
     * @param string|null          $path
     * @param array                $files
     * @param bool                 $overwrite
     *
     * @param string|null          $originalPath
     * @param string|null          $model_type
     * @param string|null          $model_id
     * @param string|null          $collection
     * @param bool                 $needJson
     * @return array
     * @throws ValidationException
     */
    final public function upload(
        User|SystemUser|null $user,
        ?string              $disk,
        ?string              $path,
                             $files,
        ?bool                $overwrite,
        ?string              $originalPath,
        ?string              $model_type = null,
        ?string              $model_id = null,
        ?string              $collection = null,
        ?bool                $needJson = true
    )
    {
        $fileNotUploaded = false;
        $fileManager = [];
        collect($files)->each(function ($v, $k) use (&$files, $disk, $originalPath, $overwrite, $model_type, $model_id, $user, $collection, $path, &$fileManager) {
            if (is_array($v) && !empty($v)) {

                // check if file exists and skip conditions
                if (Storage::disk($disk)->exists("{$originalPath}/{$k}") && !$overwrite) {
                    return;
                }

                // initiate the the file to collect chunks
                Storage::disk("local")->putFileAs("chunks", $v[0], $k);

                unset($v[0]);
                // append the the chunks to the file
                foreach ($v as $value) {
                    Storage::disk('local')->append("chunks/{$k}", $value->get());
                }

                unset($files[$k]);

                $file = new UploadedFile(Storage::disk("local")->path("chunks/{$k}"), $k);

                if (Storage::disk($disk)->exists("{$originalPath}{$k}") && $overwrite) {
                    // delete the old file
                    Storage::disk($disk)->delete("{$originalPath}{$k}");
                    // put the new file to the disk path
                    Storage::disk($disk)->putFileAs($originalPath, $file, $k);
                }

                if (Storage::disk($disk)->missing("{$originalPath}{$k}")) {
                    // add file to the path
                    Storage::disk($disk)->putFileAs($originalPath, $file, $k);
                }

                $fileManager[] = FileManagerModel::updateOrCreate([
                    'user_id' => $user->id,
                    'path' => $path,
                    'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                    'collection' => $collection ?? null,
                    'size' => $file->getSize(),
                    'model_type' => $model_type,
                    'model_id' => $model_id,

                ], [
                    'user_id' => $user->id,
                    'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                    'disk' => $disk,
                    'group' => MediaType::getGroupType($file->getClientMimeType()),
                    'path' => $path,
                    'ext' => $file->getClientOriginalExtension(),
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'model_type' => $model_type,
                    'model_id' => $model_id,
                    'collection' => $collection ?? null,
                ]);

                Storage::disk("local")->delete("chunks/{$k}");
            }
        });

        if (is_array($files)) {

            foreach ($files as $file) {
                // skip or overwrite files
                try {
                    Storage::disk($disk)
                        ->exists($originalPath . '/' . preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()));
                } catch (\Exception $e) {
                    throw ValidationException::withMessages([
                        'file' =>  $e->getMessage(),
                    ]);
                }
                if (!$overwrite
                    && Storage::disk($disk)
                        ->exists($originalPath . '/' . preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) )
                ) {
                    continue;
                }

                // check file size if need
                if ($this->configRepository->getMaxUploadFileSize()
                    && $file->getSize() / 1024 > $this->configRepository->getMaxUploadFileSize()
                ) {
                    $fileNotUploaded = true;
                    continue;
                }

                // check file type if need
                if ($this->configRepository->getAllowFileTypes()
                    && !in_array($file->getClientOriginalExtension(), $this->configRepository->getAllowFileTypes(), true)
                ) {
                    $fileNotUploaded = true;
                    continue;
                }

                $path = $path ?? "/";
                // overwrite or save file
                $storage = Storage::disk($disk)->putFileAs(
                    $originalPath,
                    $file,
                    preg_replace('/\x{AD}/u', '', $file->getClientOriginalName())
                );
                $fileManager[] = FileManagerModel::updateOrCreate([
                    'user_id' => $user->id,
                    'path' => $path,
                    'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                    'collection' => $collection ?? null,
                    'size' => $file->getSize(),
                    'model_type' => $model_type,
                    'model_id' => $model_id,

                ], [
                    'user_id' => $user->id,
                    'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                    'disk' => $disk,
                    'group' => MediaType::getGroupType($file->getClientMimeType()),
                    'path' => $path,
                    'ext' => $file->getClientOriginalExtension(),
                    'type' => $file->getClientMimeType(),
                    //'showing_columns' => '',
                    'size' => $file->getSize(),
                    'model_type' => $model_type,
                    'model_id' => $model_id,
                    'collection' => $collection ?? null,
                ]);

            }
        } else {
            $file = $files;
            if (!$overwrite
                && Storage::disk($disk)
                    ->exists($path . '/' . preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) )
            ) {
                $fileNotUploaded = true;
            }

            // check file size if need
            if ($this->configRepository->getMaxUploadFileSize()
                && $file->getSize() / 1024 > $this->configRepository->getMaxUploadFileSize()
            ) {
                $fileNotUploaded = true;
            }

            // check file type if need
            if ($this->configRepository->getAllowFileTypes()
                && !in_array($file->getClientOriginalExtension(), $this->configRepository->getAllowFileTypes(), true)
            ) {
                $fileNotUploaded = true;
            }
            $storage = Storage::disk($disk)->putFileAs(
                $originalPath,
                $file,
                preg_replace('/\x{AD}/u', '', $file->getClientOriginalName())
            );

            $fileManager = FileManagerModel::updateOrCreate([
                'user_id' => $user->id,
                'path' => $path,
                'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                'collection' => $collection,
            ], [
                'user_id' => $user->id,
                'name' => preg_replace('/\x{AD}/u', '', $file->getClientOriginalName()) ,
                'disk' => $disk,
                'group' => MediaType::getGroupType($file->getClientMimeType()),
                'path' => $path,
                'ext' => $file->getClientOriginalExtension(),
                'type' => $file->getClientMimeType(),
                //'showing_columns' => '',
                'size' => $file->getSize(),
                'model_type' => $model_type,
                'model_id' => $model_id,
                'collection' => $collection,

            ]);
        }

        // If the some file was not uploaded
        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }
        if ($needJson) {

            return [
                'result' => [
                    'status' => 'success',
                    'message' => 'uploaded',
                    'fileManager' => $fileManager
                ],
            ];
        } else {
            return $fileManager;
        }
    }

    /**
     * Rename file or folder
     *
     * @param string $diskName
     * @param string $oldPath
     * @param string $newPath
     * @param Website $tenant
     * @return void
     */
    public function rename(
        string  $diskName,
        string  $oldPath,
        string  $newPath,
        Website $tenant
    ): void
    {
        $disk = Storage::disk($diskName);

        if (!$disk->exists($oldPath)) {
            throw new LogicException(
                __('You are trying to rename a non-existent file or directory on path ":old_path"', [
                    'old_path' => $oldPath
                ])
            );
        }

        $itemType = collect($disk->listContents(dirname($oldPath)))
            ->whereStrict('path', $oldPath)
            ->pluck('type')
            ->first();

        $newPath = $this->escapeFileOrDirectoryNameInThePath($newPath);

        match ($itemType) {
            'file' => $this->renameFile($diskName, $oldPath, $newPath, $tenant),
            'dir' => $this->renameDirectory($diskName, $oldPath, $newPath, $tenant),

            default => throw new LogicException(
                __('Unknown type ":item_type" for the item on path ":item_path"', [
                    'item_type' => $itemType,
                    'item_path' => $oldPath
                ]))
        };
    }

    /**
     * Delete files and folders
     *
     * @param string $disk
     * @param array $items
     * @param string $tenant
     * @return void
     * @throws ValidationException
     */
    public function delete(
        string $disk,
        array  $items,
        string $tenant
    ): void
    {
        $deletedItems = [];

        foreach ($items as $item) {
            match ($itemType = $item['type']) {
                'file' => $this->deleteFile($disk, $item['path'], $tenant),
                'dir' => $this->deleteDirectory($disk, $item['path'], $tenant),

                default => throw ValidationException::withMessages([
                    'file' => __('Unknown type ":type" for item ":item_path"', [
                            'type' => $itemType,
                            'item_path' => $item['path']
                        ]
                    )
                ])
            };

            $deletedItems[] = $item;
        }

        event(new Deleted($disk, $deletedItems));
    }

    /**
     * Copy / Cut - Files and Directories
     *
     * @param string $disk
     * @param string $path
     * @param array $clipboard
     * @param Website $tenant
     * @return array
     */
    public function paste(
        string  $disk,
        string  $path,
        array   $clipboard,
        Website $tenant
    ): array
    {
        // compare disk names
        if ($disk !== $clipboard['disk']) {
            if (!$this->checkDisk($clipboard['disk'])) {
                return $this->notFoundMessage();
            }
        }
        foreach ($clipboard['files'] as $k => $file) {
            $file = Str::replace($tenant->uuid . '/', '', $file);
            $file = explode('/', $file);
            $fileName = end($file);
            array_pop($file);
            $oldPathfinder = implode('/', $file);


            // Extract extension and base name
            $ex = Str::afterLast($fileName, ".");
            $nameWithoutExt = Str::before($fileName, ".{$ex}");

            // Remove any existing numbering pattern like " (1)", " (2)", etc.
            $baseName = preg_replace('/\s*\(\d+\)$/', '', $nameWithoutExt);

            // Find all files that match the base name pattern
            $existingFiles = FileManagerModel::query()
                ->where([
                    ['path', $oldPathfinder],
                    ['disk', $disk]
                ])
                ->where('name', 'LIKE', $baseName . '%.' . $ex)
                ->get(['name']);

            $maxNumber = 0;
            $hasConflict = false;

            foreach ($existingFiles as $existingFile) {
                $existingNameWithoutExt = Str::before($existingFile->name, ".{$ex}");

                // Check if it's an exact match (original file)
                if ($existingNameWithoutExt === $baseName) {
                    $hasConflict = true;
                }
                // Check if it matches the pattern "basename (number)"
                elseif (preg_match('/^' . preg_quote($baseName, '/') . '\s*\((\d+)\)$/', $existingNameWithoutExt, $matches)) {
                    $hasConflict = true;
                    $maxNumber = max($maxNumber, (int)$matches[1]);
                }
            }

            // If there's a conflict, generate new filename with next available number
            if ($hasConflict) {
                $nextNumber = $maxNumber + 1;
                $fileName = "{$tenant->uuid}/{$baseName} ({$nextNumber}).{$ex}";
            } else {
                $fileName = "{$tenant->uuid}/{$fileName}";
            }

            $clipboard['to_files'][$k] = $fileName;
        }


        $transferService = TransferFactory::build($disk, $path, $clipboard);
        $res = $transferService->filesTransfer();
        $files = [];
        $path = Str::replace($tenant->uuid . '/', '', $path);

        if ($res['result']['status'] === "success") {
            $oldPathfinder = '';
            foreach ($clipboard['files'] as $k => $file) {
                $renamed = Str::replace($tenant->uuid . '/', '', $clipboard['to_files'][$k]);
                $file = Str::replace($tenant->uuid . '/', '', $file);
                $renamed_file = explode('/', $renamed);
                $file = explode('/', $file);
                $renamedFileName = end($renamed_file);
                $fileName = end($file);
                array_pop($file);
                $oldPathfinder = implode('/', $file);
                $files[] = [
                    'original' => $fileName,
                    'renamed' => $renamedFileName
                ];
            }

            if ($clipboard['type'] === "cut") {

                FileManagerModel::where('path', $oldPathfinder)
                    ->whereIn('name', collect($files)->pluck('original')->toArray())
                    ->update([
                        'path' => $path,
                        'disk' => $disk,
                    ]);
            } else {
                foreach ($files as $f) {
                    $oldFile = FileManagerModel::where([['path', $oldPathfinder], ['name', $f['original']]])->first([
                        "user_id", "name", "group", "disk", "path", "ext", "type", "showing_columns", "size", "collection", "external"
                    ])?->toArray();
                    if ($oldFile) {
                        $oldFile['name'] = $f['renamed'];
                        $oldFile['user_id'] = request()->user()?->id ?? $oldFile['user_id'];
                        $oldFile['path'] = $path;
                        $oldFile['disk'] = $disk;
                        FileManagerModel::create($oldFile);
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Download selected file
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     */
    public function download($disk, $path)
    {

        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Create thumbnails
     *
     * @param string $disk
     * @param string $imagePath
     * @param int $thumbSize
     *
     * @return Response|mixed
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function thumbnails(
        string $disk,
        string $imagePath,
        int $thumbSize
    ): mixed {
        $disk = Storage::disk($disk);

        if ($cacheTTL = Config::get('file-manager.cache')) {
            $cacheKeyForBinary = sprintf('%s.%s.%s.%s', tenant()->uuid, 'image_binary', $thumbSize, $imagePath);

            $thumbnailScaled = Cache::remember($cacheKeyForBinary, $cacheTTL, function () use ($disk, $imagePath, $thumbSize) {
                return (string)$this->imageManager->read($disk->get($imagePath))
                    ->scale(width: $thumbSize)
                    ->encodeByMediaType();
            });

            $cacheKeyForMimeType = sprintf('%s.%s.%s', tenant()->uuid, 'image_mime_type', $imagePath);

            $thumbnailMimeType = Cache::remember($cacheKeyForMimeType, $cacheTTL, function () use ($disk, $imagePath) {
                return $disk->mimeType($imagePath);
            });
        } else {
            $thumbnailScaled = $this->imageManager->read($disk->get($imagePath))
                ->scale(width: $thumbSize)
                ->encodeByMediaType();

            $thumbnailMimeType = $disk->mimeType($imagePath);
        }

        return response()->make($thumbnailScaled, 200, ['Content-Type' => $thumbnailMimeType]);
    }

    /**
     * Image preview
     *
     * @param string $disk
     * @param string $imagePath
     * @param int|null $thumbSize
     *
     * @return mixed
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function preview(
        string $disk,
        string $imagePath,
        ?int $thumbSize = 200
    ): mixed {
        $disk = Storage::disk($disk);

        [$preview, $imageMimeType] = [
            $disk->get($imagePath),
            $disk->mimeType($imagePath)
        ];

        if (in_array($imageMimeType, ['image/jpeg', 'image/png'], true)) {
            $preview = $this->imageManager->read($preview)->scale(width: $thumbSize)->encodeByMediaType();
        }

        return response()->make($preview, 200, ['Content-Type' => $imageMimeType]);
    }

    /**
     * Get file URL
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function url($disk, $path)
    {
        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'url' => Storage::disk($disk)->url($path),
        ];
    }

    /**
     * Create new directory
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createDirectory($disk, $path, $name)
    {
        // path for new directory
        $directoryName = $this->newPath($path, $name);

        // check - exist directory or no
        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'message' => __('Directory name already exists in this folder!'),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ],
            ];
        }


        // create new directory
        Storage::disk($disk)->makeDirectory($directoryName);

        // get directory properties
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        $directoryProperties = collect($directoryProperties)
            ->map(fn($value) => Str::replace(request()->tenant->uuid, '', $value) === ""
                ? '/'
                : Str::replace(request()->tenant->uuid, '', $value)
            )
            ->toArray();

        // add directory properties for the tree module
        $tree = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result' => [
                'status' => 'success',
                'message' => 'dirCreated',
            ],
            'directory' => $directoryProperties,
            'tree' => [$tree],
        ];
    }

    /**
     * Create new file
     *
     * @param string $disk
     * @param string $path
     * @param string $name
     * @param array|null $properties
     * @return void
     */
    public function createFile(
        string $disk,
        string $path,
        string $name,
        ?array &$properties = []
    ): void
    {
        $ext = Str::afterLast($name, '.');
        if ($ext === $name || empty($ext)) {
            // No extension found, add .txt as default
            $ext = 'txt';
            $name .= '.txt';

        }
        $fullPath = $this->newPath($path, $name);

        // check has extensions
       if(!in_array($ext, FileExtensions::all()->toArray(), true)) {
           throw new LogicException(__('Could not create a new file on path :path with not a valid extension :ext', ['path' => $fullPath, 'ext' => $ext]));
       }
        if (Storage::disk($disk)->exists($fullPath)) {
            throw new LogicException(__('File already exists'));
        }

        Storage::disk($disk)->put($fullPath, '') ?:
            throw new LogicException(__('Could not create a new file on path :path', ['path' => $fullPath]));

        $properties = $this->fileProperties($disk, $fullPath);
        $fileExtension = $properties['extension'] ?? '';

        FileManagerModel::create([
            'user_id' => auth()->id(),
            'name' => $properties['basename'],
            'group' => MediaType::getGroupType($fileExtension),
            'disk' => $disk,
            'path' => $this->removeTenantPrefixingFromPath($path, request()->tenant->uuid),
            'ext' => $fileExtension,
            'type' => $properties['mime-type'],
            'size' => $properties['size'],
        ]);
    }

    /**
     * Update file
     *
     * @param $disk
     * @param $path
     * @param $file
     *
     * @return array
     */
    public function updateFile($disk, $path, $file)
    {
        // update file
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        // path for new file
        $filePath = $this->newPath($path, $file->getClientOriginalName());

        // get file properties
        $fileProperties = $this->fileProperties($disk, $filePath);

        return [
            'result' => [
                'status' => 'success',
                'message' => 'fileUpdated',
            ],
            'file' => $fileProperties,
        ];
    }

    /**
     * Stream file - for audio and video
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     */
    public function streamFile($disk, $path)
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)
            ->response($path, $filename, ['Accept-Ranges' => 'bytes']);
    }

    public function StoreFileInDataBase($user, $disk, $path, $file, $model_type, $model_id, $external = false)
    {
        return FileManagerModel::updateOrCreate([
            'user_id' => $user,
            'path' => $path,
            'name' => $file->name,
            'collection' => $file->collection,
            'size' => $file->size,
            'model_type' => $model_type,
            'model_id' => $model_id,

        ], [
            'user_id' => $user,
            'name' => $file->name,
            'disk' => $disk,
            'group' => $file->group,
            'path' => $path,
            'ext' => $file->ext,
            'type' => $file->type,
            //'showing_columns' => '',
            'size' => $file->size,
            'model_type' => $model_type,
            'model_id' => $model_id,
            'collection' => $file->collection,
            'external' => $external
        ]);
    }

    public function updateMedia($id, $model_id, $model_type, $collection = null, $path = null, $disk = null)
    {
        $update = [
            'model_type' => $model_type,
            'model_id' => $model_id,
        ];
        if ($collection) {
            $update['collection'] = $collection;
        }
        if ($path) {
            $update['path'] = $path;
        }
        if ($disk) {
            $update['disk'] = $disk;
        }
        return FileManagerModel::updateOrCreate([
            'id' => $id
        ], $update);
    }
}
