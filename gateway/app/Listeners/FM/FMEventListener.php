<?php

declare(strict_types=1);

namespace App\Listeners\FM;

use Alexusmai\LaravelFileManager\Events\FileCreating;
use Alexusmai\LaravelFileManager\Events\Rename;
use App\Events\Tenant\FM\RenamedEvent;
use App\Events\Tenant\FM\RenameEvent;
use App\Events\Tenant\FM\UnzipDirectoryEvent;
use App\Events\Tenant\FM\UnzippedEvent;
use App\Events\Tenant\FM\ZipDirectoryEvent;
use App\Events\Tenant\FM\ZippedEvent;
use App\Foundation\Media\MediaType;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Services\Tenant\FM\FileManagerService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageManagerInterface;
use LogicException;

final class FMEventListener implements ShouldQueue
{
    use Dispatchable;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private readonly FileManagerService $fm,
        private readonly ImageManagerInterface $imageManager
    ) {
    }

    private function fetchFiles($paths, $event): bool
    {
        foreach ($paths as $file) {
            $disk = $event->request['disk'] === 'tenancy' ? 'tenant' : $event->request['disk'];
            $file = Str::replace($event->request['disk'] . '/', '', $file);
            $file = Str::replace($event->uuid . '/', '', $file);
            $array = explode('/', $file);
            $name = array_pop($array);
            if (str_contains($name, '.htm')) {
                $mimetype = "text/html";
            } else {
                $mimetype = Storage::disk($disk)->mimeType($file);
            }
            $path = implode('/', $array);
            $info = pathinfo(public_path($file));
            $size = Storage::disk($disk)->size($file);
            if (str_starts_with($name, ".")) {
                Storage::disk($disk)->delete($file);
                continue;
            }

            FileManagerModel::updateOrCreate([
                'user_id' => $event->request['user']->id,
                'path' => $path,
                'name' => $name,
                'collection' => 'unziped'
            ], [
                'user_id' => $event->request['user']->id,
                'name' => $name,
                'disk' => $event->request['disk'],
                'group' => MediaType::getGroupType($mimetype),
                'path' => $path,
                'ext' => $info['extension'],
                'type' => $mimetype,
                'size' => $size,
                'collection' => 'unziped',

            ]);
        }
        return true;
    }

    /**
     * @param $event
     * @throws GuzzleException
     */
    public function onFileUnzipped($event): void
    {
        try {
            $res = $this->fm->extract($event->request);
            if ($res['status'] === 200) {
                $url_arr = explode('/', $event->request['to']);
                $event->uuid = array_shift($url_arr);
                $this->fetchFiles($res['paths'], $event);
                UnzippedEvent::broadcast($event->request);
            }
        } catch (Exception $e) {
            Log::info("FMEventListener=>onFileUnzipped", [$e, $event]);
        }
    }

    /**
     * @param $event
     * @throws GuzzleException
     */
    public function onFileZipped($event): void
    {
        try {
            $res = $this->fm->zip($event->request);
            if ($res['status'] === 200) {
                $url_arr = explode('/', $event->request['path']);
                $event->uuid = array_shift($url_arr);
                ZippedEvent::broadcast($res, $event->uuid);
            }
        } catch (Exception $e) {
            Log::info("FMEventListener=>onFileZipped", [$e, $event]);
        }
    }


    /**
     * @param RenameEvent $event
     * @throws GuzzleException
     */
    public function onDirectoryRename(RenameEvent $event): void
    {
        try {
            $res = $this->fm->rename($event->request);

            if ($res['status'] === 200) {
                RenamedEvent::broadcast($event->request);
            }
        } catch (Exception $e) {
            Log::info("FMEventListener=>onFileZipped", [$e, $event]);
        }
    }

    public function onFileCreating(FileCreating $event): void
    {
        if ($this->isFileHasAnImageExtension($event->name())) {
            throw new LogicException('You cannot create an empty file with an image extension.');
        }
    }

    public function onFileRename(Rename $event): void
    {
        if ($this->isFileHasAnImageExtension($event->newName())) {
            $existingImageFileFullPath = sprintf('%s/%s', tenant()->uuid, $event->oldName());

            try {
                $this->imageManager->read(Storage::disk($event->disk())->get($existingImageFileFullPath));
            } catch (Exception) {
                throw new LogicException(
                    "You have tried to change the file extension to an image one, but current binary data is not valid image data."
                );
            }
        }
    }

    public function subscribe($events): void
    {
        $events->listen(UnzipDirectoryEvent::class, [$this, 'onFileUnzipped']);
        $events->listen(ZipDirectoryEvent::class, [$this, 'onFileZipped']);

        $events->listen(FileCreating::class, [$this, 'onFileCreating']);

        $events->listen(Rename::class, [$this, 'onFileRename']);
        $events->listen(RenameEvent::class, [$this, 'onDirectoryRename']);
    }

    public function failed($event, $exception): void
    {
        Log::info("FMEventListener=>failed", [$exception, $event]);
    }

    private function isFileHasAnImageExtension(string $fileNameOrPath): bool
    {
        return ($fileExtension = pathinfo($fileNameOrPath, PATHINFO_EXTENSION))
            && in_array($fileExtension, Config::get('file-manager.extensionsToConsiderFilesAsImages'), true);
    }
}
