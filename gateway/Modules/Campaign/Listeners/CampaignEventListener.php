<?php

namespace Modules\Campaign\Listeners;

use App\Events\FinishedExportCampaignEvent;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Services\Converters\ConverterService;
use App\Services\Tenant\FM\FileManagerService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Campaign\Entities\CampaignExport;
use Modules\Campaign\Events\CreateCampaignFolderEvent;
use Modules\Campaign\Events\GenerateCampaignEvent;


//implements ShouldQueue
class CampaignEventListener implements ShouldQueue
{
    /**
     * @param FileManagerService $fm
     * @param ConverterService   $converterService
     */
    public string $uuid;

    public function __construct(protected ConverterService $converterService, protected FileManagerService $fm)
    {
    }

    /**
     * @param $event
     * @throws GuzzleException
     */
    public function onCampaignCreated($event)
    {
        $this->uuid = $event->uuid;
        if (!Storage::disk('tenant')->exists("campaigns/{$event->campaign->slug}")) {
            Storage::disk('tenant')->makeDirectory("campaigns/{$event->campaign->slug}");
        }
        $rows = $this->fm->readExcel(['path' => $this->addUuidIfNotExists("{$event->campaign->file->path}/{$event->campaign->file->name}")]);
        $count = count($rows['data']);
        $event->campaign->config = array_merge($event->campaign->config, ['totalPartners' => $count, 'export_deleted' => false]);
        $event->campaign->save();
    }

    /**
     * @param $event
     */
    public function onCampaignGenerate($event)
    {
        $template = $event->campaign->providerTemplates->where('id', $event->template->id)->first();
        $config = $event->campaign->config;
        $file = $event->campaign->file;
        $this->uuid = $event->uuid;
        $export = $event->export;
        $response = ['message' => __("Campaign {$event->campaign->name} generated successfully"), 'status' => 200];
        $currentRow = array_pop($event->rows);
        $disk = 'tenancy';
        if ($config['output'] === 'partner_first') {
            /**
             * find html only from the template
             */
            /** start reading the excl */
            collect($template->assets->where('type', 'text/html'))->map(function ($html) use ($config, $file, $template, $event, $export, $currentRow, $disk) {
                /** @var  $htmInput ... html input */

                $htmInput = Storage::disk($disk)->get($this->addUuidIfNotExists("{$html->path}/{$html->name}"));

                /** check if directory exists */
                if (!Storage::disk($disk)->has($this->addUuidIfNotExists("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}"))) {
                    /** create directory if not exists */
                    Storage::disk('tenant')
                        ->makeDirectory("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}");
//                    if (env('APP_ENV') === 'local') {
//                        $this->LocalTemplateCopy($html, $file, $currentRow, $template, $event->time);
//                    } else {
                        $this->cloudTemplateCopy($html, $event, $currentRow, $file, $template, $event->time);
//                    }
                }

//                dd($html, $event, $currentRow, $file, $template, $event->time, 'sss');
                /**
                 * @var  $k .. column name
                 * @var  $v .. $value
                 */
                foreach ($currentRow as $k => $v) {
                    /** @var … replace all the available tags with excl value $htmInput */
                    $htmInput = preg_replace_callback('/\[\[' . $k . ']]/', function ($matches) use ($v) {
                        return $v;
                    }, $htmInput);
                }

                /** save the html file */
                Storage::disk('tenant')
                    ->put(
                        "{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}/{$html->name}", $htmInput
                    );
                /**
                 * zip campaign
                 */
                $results = $this->fm->zip([
                    'path' => $this->addUuidIfNotExists("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}"),
                    'name' => "{$template->name} {$event->time}",
                    'disk' => $disk,
                    'is_dir' => '1',
                    'putPath' => $this->addUuidIfNotExists("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}")
                ]);
                if ($results['status'] === 200) {
                    // Storage::disk('tenant')->deleteDirectory("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}");
                    if (env('APP_ENV') === 'local') {
                        $url = "https://google.com";
                    } else {
                        $url = Storage::disk('tenant')->url("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}/{$html->name}");
                    }
                    $links = [
                        'download' => "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}.zip",
                        'preview' => $url
                    ];
                    if ($config['exportImage']) {
                        $this->exportAsImage($html, $url, $currentRow, $file, $event, $template, $event->time, $export, $links);
                    }
                    if ($config['exportPdf']) {
                        $this->exportAsPdf($html, $url, $currentRow, $file, $event, $template, $event->time, $export, $links);
                    }
                    $path = $export->path ?? [];
                    $export->update([
                        'path' => array_merge(
                            $path, [
                                $currentRow['Partner'] => $links
                            ]
                        ),
                    ]);
                    $size = Storage::disk('tenant')->size("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}.zip");

                    FileManagerModel::updateOrCreate([
                        'user_id' => $event->user->id,
                        'path' => "{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}",
                        'name' => "{$template->name} {$event->time}.zip",
                        'collection' => 'campaign-partner-export'
                    ], [
                        'user_id' => $event->user->id,
                        'name' => "{$template->name} {$event->time}.zip",
                        'disk' => 'tenancy',
                        'group' => 'others',
                        'path' => "{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}",
                        'ext' => 'zip',
                        'type' => 'application/zip',
                        'size' => $size,
                        'model_type' => CampaignExport::class,
                        'model_id' => $export->id,
                        'collection' => 'campaign-partner-export',

                    ]);
                } else {
//                    Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] === partner_first && zipping failed", [$results, $event]);
                    $response = ['message' => __('We couldn\'t generate the zip file'), 'status' => 400];
                    $this->CheckEventFinished($event, $export, $response);
                    return false;
                }

            });

        } else {
            /**
             * find html only from the template
             */
            collect($template->assets->where('type', 'text/html'))->map(function ($html) use ($config, $file, $template, $event, $export, &$response, $currentRow, $disk) {
                /** @var  $htmInput ... html input */
                $htmInput = Storage::disk($disk)->get($this->addUuidIfNotExists("{$html->path}/{$html->name}"));
                /** start reading the excl */
                /** check if directory exists */
                if (!Storage::disk($disk)->has($this->addUuidIfNotExists("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}"))) {
                    /** create directory if not exists */
                    Storage::disk('tenant')
                        ->makeDirectory("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}");
                    /** copy all the template files */
                    $s3 = Storage::disk($disk);

                    $files = Storage::disk($disk)->allFiles($html->path);
                    foreach ($files as $singleFile) {
                        $prefix = (env('APP_ENV') === "local") ? '' : "{$event->uuid}/";
                        $new_loc = str_replace($html->path, $this->addUuidIfNotExists("{$prefix}{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}"), $singleFile);
                        $s3->copy($singleFile, $new_loc);
                        $s3->setVisibility($new_loc, 'public');
                    }

                    /**
                     * copy partner images
                     *
                     */
                    $files = Storage::disk($disk)->allFiles($this->addUuidIfNotExists("{$template->pivot->assets}/{$currentRow['Partner']}"));
                    foreach ($files as $singleFile) {
                        $new_loc = str_replace("{$template->pivot->assets}/{$currentRow['Partner']}", "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}", $singleFile);
                        $s3->copy($singleFile, $new_loc);
                        $s3->setVisibility($new_loc, 'public');

                    }
                }

                /**
                 * create a export row
                 */

                /**
                 * @var  $k .. column name
                 * @var  $v .. $value
                 */
                foreach ($currentRow as $k => $v) {
                    /** @var … replace all the available tags with excl value $htmInput */
                    $htmInput = preg_replace_callback('/\[\[' . $k . ']]/', function ($matches) use ($v) {
                        return $v;
                    }, $htmInput);
                }

                /** save the html file */
                Storage::disk('tenant')
                    ->put(
                        "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$html->name}", $htmInput
                    );
                /**
                 * zip campaign
                 */
                $results = $this->fm->zip([
                    'path' => $this->addUuidIfNotExists("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}"),
                    'putPath' => $this->addUuidIfNotExists("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}"),
                    'name' => "{$currentRow['Partner']} {$event->time}",
                    'disk' => $disk,
                    'is_dir' => '1'
                ]);
                if ($results['status'] === 200) {
                    // Storage::disk('tenant')->deleteDirectory("{$file->path}/output/{$currentRow['Partner']}/{$template->name} {$event->time}");
                    if (env('APP_ENV') === 'local') {
                        $url = "https://google.com";
                    } else {
                        $url = Storage::disk('tenant')->url("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$html->name}");
                    }
                    $links = [
                        'download' => "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}.zip",
                        'preview' => $url
                    ];
                    if ($config['exportImage']) {
                        $jpgFileName = str_replace('.htm', "", $html->name);
                        $jpg = $this->converterService
                            ->convert([
                                "url" => $url,
                                "type" => "jpeg",
                                "name" => $jpgFileName,
                                "quality" => 100,
                                "deviceScaleFactor" => 3,
                                "options" => ["noSandbox", "fullPage", "showBackground"]
                            ]);
                        if (is_array($jpg) && $jpg['status'] === 400) {
//                            Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] !== partner_first && generating jpg failed", [$jpg, $event]);
                            $response = ['message' => __('We couldn\'t generate jpg file successfully.'), 'status' => 400];
                            return false;
                        }
                        Storage::disk('tenant')->put(
                            "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$jpgFileName}.jpg",
                            $jpg
                        );
                        $links['download_jpg'] = "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$jpgFileName}.jpg";
                        $links['preview_jpg'] = Storage::disk('tenant')->url("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$jpgFileName}.jpg");
                        $size = Storage::disk('tenant')->size("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$jpgFileName}.jpg");
                        FileManagerModel::updateOrCreate([
                            'user_id' => $event->user->id,
                            'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                            'name' => "{$jpgFileName}.jpg",
                            'collection' => 'campaign-partner-export'
                        ], [
                            'user_id' => $event->user->id,
                            'name' => "{$jpgFileName}.jpg",
                            'disk' => 'tenancy',
                            'group' => 'others',
                            'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                            'ext' => 'jpg',
                            'type' => 'image/jpg',
                            'size' => $size,
                            'model_type' => CampaignExport::class,
                            'model_id' => $export->id,
                            'collection' => 'campaign-partner-export',
                        ]);
                    }
                    if ($config['exportPdf']) {
                        $pdfFileName = str_replace('.htm', "", $html->name);

                        $pdf = $this->converterService
                            ->convert([
                                "url" => $url,
                                "type" => "pdf",
                                "name" => $pdfFileName,
                                // "format"=>"a2",
                                "quality" => 100,
                                "options" => ["noSandbox", "fullPage", "showBackground"]
                            ]);
                        if (is_array($pdf) && $pdf['status'] === 400) {
//                            Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] !== partner_first && generating pdf failed", [$pdf, $event]);
                            $response = ['message' => __('We couldn\'t generate pdf file successfully.'), 'status' => 400];
                            return false;
                        }
                        Storage::disk('tenant')->put(
                            "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$pdfFileName}.pdf",
                            $pdf
                        );
                        $links['download_pdf'] = "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$pdfFileName}.pdf";
                        $links['preview_pdf'] = Storage::disk('tenant')->url("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$pdfFileName}.pdf");
                        $size = Storage::disk('tenant')->size("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}/{$pdfFileName}.pdf");
                        FileManagerModel::updateOrCreate([
                            'user_id' => $event->user->id,
                            'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                            'name' => "{$pdfFileName}.pdf",
                            'collection' => 'campaign-partner-export'
                        ], [
                            'user_id' => $event->user->id,
                            'name' => "{$pdfFileName}.pdf",
                            'disk' => 'tenancy',
                            'group' => 'others',
                            'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                            'ext' => 'pdf',
                            'type' => 'application/pdf',
                            'size' => $size,
                            'model_type' => CampaignExport::class,
                            'model_id' => $export->id,
                            'collection' => 'campaign-partner-export',
                        ]);
                    }
                    $path = $export->path ?? [];
                    $export->update([
                        'path' => array_merge(
                            $path, [
                                $currentRow['Partner'] => $links
                            ]
                        ),
                    ]);
                    $size = Storage::disk('tenant')->size("{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}.zip");

                    FileManagerModel::updateOrCreate([
                        'user_id' => $event->user->id,
                        'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                        'name' => "{$currentRow['Partner']} {$event->time}.zip",
                        'collection' => 'campaign-partner-export'
                    ], [
                        'user_id' => $event->user->id,
                        'name' => "{$currentRow['Partner']} {$event->time}.zip",
                        'disk' => 'tenancy',
                        'group' => 'others',
                        'path' => "{$file->path}/output/{$template->name}/{$currentRow['Partner']} {$event->time}",
                        'ext' => 'zip',
                        'type' => 'application/zip',
                        'size' => $size,
                        'model_type' => CampaignExport::class,
                        'model_id' => $export->id,
                        'collection' => 'campaign-partner-export',

                    ]);
                } else {
//                    Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] !== partner_first && zipping failed", [$results, $event]);
                    $response = ['message' => __('We couldn\'t generate zip file successfully.'), 'status' => 400];
                    return false;
                }


                if ($response['status'] === 400) {
                    return false;
                }
            });
        }
        $this->CheckEventFinished($event, $export, $response);
    }


    public function failed($event, $exception)
    {
        Log::info($exception, [$event]);
    }

    /**
     * @param        $html
     * @param        $file
     * @param        $row
     * @param mixed  $template
     * @param string $time
     */
    public function LocalTemplateCopy($html, $file, $row, mixed $template, string $time): void
    {
        /** copy all the template files */
        File::copyDirectory(
            Storage::disk('tenancy')->path($html->path),
            Storage::disk('tenancy')->path("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}")
        );

        /** copy partner images */
        File::copyDirectory(
            Storage::disk('tenancy')->path("{$template->pivot->assets}/{$row['Partner']}"),
            Storage::disk('tenancy')->path("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}")
        );

    }

    /**
     * @param        $html
     * @param        $event
     * @param        $row
     * @param        $file
     * @param mixed  $template
     * @param string $time
     */
    public function cloudTemplateCopy($html, $event, $row, $file, mixed $template, string $time): void
    {
        /** copy all the template files */
        $s3 = Storage::disk('tenancy');

        $files = Storage::disk('tenancy')->allFiles($this->addUuidIfNotExists($html->path));
        foreach ($files as $singleFile) {
            $new_loc = str_replace($html->path, "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}", $singleFile);
            if (!$s3->exists($new_loc)) {
                $s3->copy($singleFile, $this->addUuidIfNotExists($new_loc));
                $s3->setVisibility($this->addUuidIfNotExists($new_loc), 'public');
            }
        }
        /**
         * copy partner images
         *
         */
        $files = Storage::disk('tenancy')->allFiles($this->addUuidIfNotExists("{$template->pivot->assets}/{$row['Partner']}"));
        foreach ($files as $singleFile) {
            $new_loc = str_replace("{$template->pivot->assets}/{$row['Partner']}", "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}", $singleFile);
            if (!$s3->exists($new_loc)) {
                $s3->copy($singleFile, $new_loc);
                $s3->setVisibility($new_loc, 'public');
            }
        }
    }

    private function exportAsImage($html, $url, $row, $file, $event, $template, $time, $export, &$links)
    {
        $jpgFileName = str_replace(['.htm', '.html'], ["", ""], $html->name);

        $jpg = $this->converterService
            ->convert([
                "url" => $url,
                "type" => "jpeg",
                "name" => $jpgFileName,
                "deviceScaleFactor" => 5,
                "quality" => 100,
                "options" => ["fullPage", "noSandbox", "showBackground"],
            ]);
        if (is_array($jpg) && $jpg['status'] === 400) {
//            Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] === partner_first && generating jpg failed", [$jpg, $event]);
            $response = ['message' => __('We couldn\'t generate jpg image.'), 'status' => 400];
            return false;
        }
        Storage::disk('tenancy')->put(
            $this->addUuidIfNotExists("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$jpgFileName}.jpg"),
            $jpg
        );
        $links['download_jpg'] = "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$jpgFileName}.jpg";
        $links['preview_jpg'] = Storage::disk('tenancy')->url($this->addUuidIfNotExists("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$jpgFileName}.jpg"));
        $size = Storage::disk('tenancy')->size($this->addUuidIfNotExists("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$jpgFileName}.jpg"));
        FileManagerModel::updateOrCreate([
            'user_id' => $event->user->id,
            'path' => "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}",
            'name' => "{$jpgFileName}.jpg",
            'collection' => 'campaign-partner-export'
        ], [
            'user_id' => $event->user->id,
            'name' => "{$jpgFileName}.jpg",
            'disk' => 'tenancy',
            'group' => 'others',
            'path' => "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}",
            'ext' => 'jpg',
            'type' => 'image/jpg',
            'size' => $size,
            'model_type' => CampaignExport::class,
            'model_id' => $export->id,
            'collection' => 'campaign-partner-export',
        ]);
    }

    private function exportAsPdf($html, $url, $row, $file, $event, $template, $time, $export, &$links)
    {
        $pdfFileName = str_replace('.htm', "", $html->name);

        $pdf = $this->converterService
            ->convert([
                "url" => $url,
                "type" => "pdf",
                "name" => $pdfFileName,
                "quality" => 100,
                // "format"=>"a2",
                "options" => ["noSandbox", "fullPage", "showBackground"]
            ]);
        if (is_array($pdf) && $pdf['status'] === 400) {
//            Log::info("CampaignEventListener=>onCampaignGenerate \$config[output] === partner_first && generating pdf failed", [$pdf, $event]);
            $response = ['message' => __('We couldn\'t generate pdf file successfully.'), 'status' => 400];
            return false;
        }
        Storage::disk('tenant')->put(
            "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$pdfFileName}.pdf",
            $pdf
        );
        $links['download_pdf'] = "/media-manager/file-manager/download?disk=tenancy&path={$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$pdfFileName}.pdf";
        $links['preview_pdf'] = Storage::disk('tenant')->url("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$pdfFileName}.pdf");
        $size = Storage::disk('tenant')->size("{$file->path}/output/{$row['Partner']}/{$template->name} {$time}/{$pdfFileName}.pdf");
        FileManagerModel::updateOrCreate([
            'user_id' => $event->user->id,
            'path' => "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}",
            'name' => "{$pdfFileName}.pdf",
            'collection' => 'campaign-partner-export'
        ], [
            'user_id' => $event->user->id,
            'name' => "{$pdfFileName}.pdf",
            'disk' => 'tenancy',
            'group' => 'others',
            'path' => "{$file->path}/output/{$row['Partner']}/{$template->name} {$time}",
            'ext' => 'pdf',
            'type' => 'application/pdf',
            'size' => $size,
            'model_type' => CampaignExport::class,
            'model_id' => $export->id,
            'collection' => 'campaign-partner-export',
        ]);
    }

    /**
     * @param       $event
     * @param       $export
     * @param array $response
     */
    public function CheckEventFinished($event, $export, array $response)
    {
        /**
         * Response 400
         * Response 200
         * Rows is empty
         */
        if (empty($event->rows)) {
            $config = $event->campaign->config ?? [];

            $event->campaign->update([
                'config' => array_merge($config, ['export' => 'done', 'export_deleted' => false])
            ]);
            $export->update([
                'finished' => true
            ]);
            event(new FinishedExportCampaignEvent($event->campaign, CampaignExport::find($event->export->id)->first(), $response));
            return false;
        } else {
            $event->export = CampaignExport::find($event->export->id);
            event(new GenerateCampaignEvent($event->campaign, $event->uuid, $event->domain, $event->user, $event->rows, $event->time, $event->export, $event->template));
        }


    }

    public function addUuidIfNotExists($url)
    {
        return Str::startsWith($url, $this->uuid) ? $url : $this->uuid . DIRECTORY_SEPARATOR . $url;
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateCampaignFolderEvent::class,
            'Modules\Campaign\Listeners\CampaignEventListener@onCampaignCreated'
        );

        $events->listen(
            GenerateCampaignEvent::class,
            'Modules\Campaign\Listeners\CampaignEventListener@onCampaignGenerate'
        );


    }
}
