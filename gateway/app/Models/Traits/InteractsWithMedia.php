<?php

namespace App\Models\Traits;

use App\Foundation\Media\FileManager;
use App\Foundation\Media\FileManager as MediaFileManager;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Models\Tenants\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait InteractsWithMedia
{

    /**
     * @var string
     */
    public string $diskName = 'tenancy';

    public function getOriginalPath($path = '/')
    {
        if ($path !== "/") {
            $path = '/' . $path;
        }

        return Str::replace('//', '/', '/' . tenant()?->uuid . $path);
    }

    /**
     * @param        $file
     * @param string $path
     * @param null|int|bool $overwrite
     * @param null|string $originalPath
     * @param null   $collection
     * @param bool $needJson
     * @param string $disk
     * @return JsonResponse
     */
    public function addMedia(
        $file,
        string $path = "/",
        null|int|bool $overwrite = 0,
        ?string $originalPath = '/',
        $collection = null,
        bool $needJson = true,
        string $disk = 'tenancy'
    )
    {

        $app = app(FileManager::class);
        $uploadResponse = $app->upload(
            auth()->user(),
            $disk,
            $path,
            $file,
            (bool)$overwrite,
            $this->getOriginalPath($originalPath),
            get_class($this),
            $this->id ?? null,
            $collection,
            $needJson
        );
        return response()->json($uploadResponse);
    }

    public function addFirstMedia(
        $file,
        $path = "/",
        $overwrite = 0,
        $originalPath = '/',
        $collection = null,
        $needJson = true,
        $disk = 'tenancy'
    )
    {
        $this->removeMedia($collection);

        return response()->json($this->addMedia(
            $file,
            $path,
            $overwrite,
            $originalPath,
            $collection,
            $needJson,
            $disk,
        ));
    }

    public function getFirstMedia(
        ?string $collection = null,
        string  $resType = "url",
        string  $disk = "tenancy"
    )
    {
        return $this->getMedia($collection, $resType, $disk)->first();
    }

    public function media($val = null)
    {
        return $this->morphMany(FileManagerModel::class, 'file_manager',
            'model_type', 'model_id');
    }

    /**
     * return url from a file manager model
     * @param FileManagerModel
     * @return string
     */
    public function getImageUrlFromFileManagerModel(FileManagerModel $fm)
    {
        return Storage::disk($fm->disk)->url($this->getImagePathFromFileManagerModel($fm));
    }

    /**
     * takes the file manager model to generate the path for a media
     * remove multiple slashes from it
     * @param FileManagerModel
     * @return string
     */
    public function getImagePathFromFileManagerModel(FileManagerModel $fm)
    {
        $path = $fm->path;
        if (isset(request()->tenant) && isset(request()->tenant->uuid)) {
            $path = str_replace(request()->tenant->uuid, '', $path);
        }

        return preg_replace_callback( // to remove additional slashes / in path
            '/\/+/',
            fn () => '/',
            request()->tenant->uuid . '/' . $path . '/' . $fm->name
        );
    }

    /**
     * @param string|null $collection
     * @param string      $resType
     * @return mixed
     */
    public function getMedia(
        ?string $collection = null,
        string  $resType = "url",
        string  $disk = "tenancy"
    )
    {
        $results = [];
        $where = [
            ['model_type', get_class($this)],
            ['model_id', $this->id],
        ];
        if ($collection) {
            $where[] = ['collection', $collection];
        }
        if ($disk) {
            $where[] = ['disk', $disk];
        }
        return FileManagerModel::where($where)->get();
    }

    public function getImagePath($path, $name)
    {
        if (isset(request()->tenant) && isset(request()->tenant->uuid)) {
            $path = str_replace(request()->tenant->uuid, '', $path);
        }
        return $path . '/' . $name;
    }

    public function getPrivateFileUrl(FileManagerModel $file)
    {
        return $this->getScureURl('api/v1/en/mgr/media-manager/file-manager/preview?disk=' . $this->diskName . '&path=' . $this->getImagePath($file));
    }

    public function getPublicFileUrl($disk, $path, $name)
    {
        return $this->getScureURl('api/v1/en/mgr/media-manager/file-manager/public?disk=' . $disk . '&path=' . $this->getImagePath($path, $name));
    }

    public function getDownloadFileUrl($disk, $path, $name)
    {

        return env('APP_URL') . '/api/v1/en/mgr/media-manager/file-manager/download?disk=' . $disk . '&path=' . $this->getImagePath($path, $name);
    }

    public function getScureURl($path = '')
    {
        return env('APP_ENV', 'production') == 'production' ? secure_url($path) : url($path);
    }

    /**
     * @param string $collection
     * @return mixed
     */
    final public function removeMedia(
        string $collection
    ): bool
    {
        return FileManagerModel::where([
            ['model_type', get_class($this)],
            ['model_id', $this->id],
            ['collection', $collection]
        ])->delete();
    }

    public function addMediaCrossTenant(
        object $media,
        string $sourceTenantPath,
        string $targetTenantPath,
        object $targetModel,
        string $newRelativePath
    ): void {
        // Build full paths
        $sourcePath = $sourceTenantPath . $media->path . $media->name;
        $newMedia = $media->replicate();
        $newMedia->path = $newRelativePath;
        $newMedia->model_id = $targetModel->id;
        $destinationPath = $targetTenantPath . $newMedia->path . $newMedia->name;
        Storage::disk($media->disk)->copy($sourcePath, $destinationPath);
        $newMedia->save();
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
}
