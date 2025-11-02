<?php

namespace App\Listeners\DesignTemplate;

use Alexusmai\LaravelFileManager\Services\Zip;
use App\Events\Tenant\DesignTemplate\CreateTemplateEvent;
use App\Events\Tenant\FM\FinishedExtractingDesignProviderTemplate;
use App\Foundation\Media\MediaType;
use App\Http\Requests\FileManager\RequestValidator;
use App\Models\Tenants\DesignProviderTemplate;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Services\Tenant\FM\FileManagerService;
use Aws\S3\S3Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

//implements ShouldQueue
class DesignTemplateEventListener implements ShouldQueue
{
    use InteractsWithQueue, Dispatchable;

    /**
     * @param FileManagerService $fm
     */
    public function __construct(public FileManagerService $fm){}


    private function fetchFiles(array $paths, $event): bool
    {
        foreach ($paths as $file) {
            $file = str_replace($event->tenant . '/', '', $file);
            $array = explode('/', $file);
            $name = array_pop($array);
            if (str_contains($name, '.htm')) {
                $mimetype = "text/html";
            } else {
                $mimetype = Storage::disk('tenant')->mimeType($file);
            }
            $path = implode('/', $array);
            $info = pathinfo(public_path($file));
            $size = Storage::disk('tenant')->size($file);
            if (str_starts_with($name, ".")) {
                Storage::disk('tenant')->delete($file);
                continue;
            }

            FileManagerModel::updateOrCreate([
                'user_id' => $event->user->id,
                'path' => $path,
                'name' => $name,
                'collection' => 'design-provider-templates'
            ], [
                'user_id' => $event->user->id,
                'name' => $name,
                'disk' => 'tenancy',
                'group' => MediaType::getGroupType($mimetype),
                'path' => $path,
                'ext' => $info['extension'],
                'type' => $mimetype,
                'size' => $size,
                'model_type' => DesignProviderTemplate::class,
                'model_id' => $event->designProviderTemplate->id,
                'collection' => 'design-provider-templates',

            ]);

        }
        return true;
    }

    /**
     * @param $event
     */
    final public function onTemplateCreated(
        $event
    ): void
    {
        $path = collect($event->designProviderTemplate->assets)->first();
        cloneData(
            'tenancy',
            $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name,
            'local',
            $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name,
        );

        $request = new RequestValidator([
            'disk' => 'local',
            'folder' => null,
            'path' => $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name
        ]);
        $zip = new ZipArchive();
        $zip = new Zip($zip, $request);
        if ($zip->extract()) {
            // delete zip file
            Storage::disk('local')->delete(
                $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name
            );
            $event->designProviderTemplate->removeMedia('design-provider-templates');

            Storage::disk('tenancy')->delete(
                $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name
            );

            $storage = Storage::disk('local')->listContents($event->tenant . DIRECTORY_SEPARATOR . $path->path);
            $dir = [];
            foreach ($storage as $directory) {
                if (str_contains($directory['basename'], "__MACOSX")) {
                    Storage::disk('local')->delete($directory['path']);
                    Storage::disk('local')->deleteDirectory($directory['path']);
                } else {
                    $dir[] = $directory;
                    foreach (Storage::disk('local')->files($directory['path'], true) as $file) {
                        $mimetype = Storage::disk('local')->mimeType($file);

                        $array = explode('/', $file);
                        $name = array_pop($array);
                        $path = implode('/', $array);
                        $info = pathinfo(public_path($file));

                        $size = Storage::disk('local')->size($file);

                        if (str_starts_with($name, ".")) {
                            Storage::disk('local')->delete($file);
                            continue;
                        }

                        FileManagerModel::updateOrCreate([
                            'user_id' => $event->user->id,
                            'path' => $path,
                            'name' => $name,
                            'collection' => 'design-provider-templates'
                        ], [
                            'user_id' => $event->user->id,
                            'name' => $name,
                            'disk' => 'tenancy',
                            'group' => MediaType::getGroupType($mimetype),
                            'path' => $path,
                            'ext' => $info['extension'],
                            'type' => $mimetype,
                            'size' => $size,
                            'model_type' => DesignProviderTemplate::class,
                            'model_id' => $event->designProviderTemplate->id,
                            'collection' => 'design-provider-templates',

                        ]);

                    }
                }

            }

            if (env('FILESYSTEM_DRIVER') !== 'local') {
                $s3 = new S3Client([
                    'credentials' => [
                        'key' => env('DIGITALOCEAN_SPACES_KEY'),
                        'secret' => env('DIGITALOCEAN_SPACES_SECRET'),
                    ],
                    'region' => env('DIGITALOCEAN_SPACES_REGION'),
                    'endpoint' => env('DIGITALOCEAN_SPACES_ENDPOINT'),
                    'version' => 'latest'
                ]);
                collect($dir)->each(function($d) use ($s3){
                    $s3->uploadDirectory(
                        Storage::disk('local')->path($d['path']),
                        env('DIGITALOCEAN_SPACES_BUCKET'),
                        'tenancy' . DIRECTORY_SEPARATOR . $d['path']
                    );
                    Storage::disk('local')->deleteDirectory($d['dirname']);
                });
            }
            event(new FinishedExtractingDesignProviderTemplate($event->designProviderTemplate));
        }
    }

    /**
     * @param $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    final public function onTemplateCreatedOld(
        $event
    ): void
    {
        $path = collect($event->designProviderTemplate->assets)->first();
        $zip = $this->fm->extract([
            'disk' => 'tenancy',
            'folder' => null,
            'file' => $event->tenant . DIRECTORY_SEPARATOR . $path->path . DIRECTORY_SEPARATOR . $path->name,
            'to' => $event->tenant . DIRECTORY_SEPARATOR . $path->path
        ]);

        if ($zip['status'] === 200) {
            Storage::disk('tenant')->delete($path->path . DIRECTORY_SEPARATOR . $path->name);
            $event->designProviderTemplate->removeMedia('design-provider-templates');
            $this->fetchFiles($zip['paths'], $event);
            event(new FinishedExtractingDesignProviderTemplate($event->designProviderTemplate));
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateTemplateEvent::class,
            'App\Listeners\DesignTemplate\DesignTemplateEventListener@onTemplateCreated'
        );


    }

    public function failed($event, $exception)
    {
        dd($exception, $event);
    }
}
