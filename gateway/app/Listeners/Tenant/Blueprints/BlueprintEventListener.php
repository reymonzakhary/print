<?php

namespace App\Listeners\Tenant\Blueprints;

use App\Events\Tenant\Blueprints\AddLayerEvent;
use App\Events\Tenant\Blueprints\AddLayerOnPositionEvent;
use App\Events\Tenant\Blueprints\AddStampOnPositionEvent;
use App\Events\Tenant\Blueprints\ConverterPdfEvent;
use App\Events\Tenant\Blueprints\FailedBlueprintRunnerEvent;
use App\Events\Tenant\Blueprints\MergeFilesEvent;
use App\Events\Tenant\Blueprints\MergePdfEvent;
use App\Events\Tenant\Blueprints\RemoveFilesEvent;
use App\Events\Tenant\Blueprints\ReplaceStringOnPdfEvent;
use App\Events\Tenant\Blueprints\SeparatePdfEvent;
use App\Services\PdfCo\PdfCoService;
use App\Services\Pdftool\PdftoolService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
use Log;
use RuntimeException;

class BlueprintEventListener implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * remove files from array
     * @param $event
     */
    public function removeFiles($event)
    {
        Storage::disk($event->disk)->delete($event->files);
    }

    /**
     * @param $event
     */
    public function AddStampOnPosition($event)
    {
        $counter = 30;
        $output = "{$event->output_path}{$event->row[$event->ref]}_render_output.pdf";

        $result = app(PdftoolService::class)->addLayerOnPosition(
            origin: $event->origin,
            stamp: $event->stamp,
            x: $event->x,
            y: $event->y,
            page: $event->page,
            search: $event->search,
            act: $event->act,

        );

        if (optional($result)['status'] === 200) {

            do {
                if ($counter === 0 || Storage::disk('local')->exists($output)) {
                    Storage::disk('local')->delete($event->replace);
                    Storage::disk('local')->move($output, $event->replace);
                    $path = Storage::disk('local')->path($event->replace);
                    Artisan::call("permission:convert {$path} ");
                    break;
                }
                $counter = $this->getCounter($result, $output, $counter);
            } while (true);

            Storage::disk('local')
                ->put(
                    "{$event->tmp_output_dir}/output-layer-added-on-position-{$event->row[$event->ref]}.pdf",
                    "finished {$event->row[$event->ref]}"
                );
        }
    }

    /**
     *
     * public string $signature,
     * public int  $page,
     * public string $path,
     * public string $disk,
     * public string $list,
     * public string $layer,
     * public ?array $array,
     * public ?string $ref
     * @param $event
     */
    public function addLayerOnPosition($event)
    {
        $counter = 30;
        $background = Storage::disk('local')->path($event->path);
        $layer = Storage::disk('local')->path($event->layer);
        $output = Storage::disk('local')->path("{$event->output_path}/output-{$event->row[$event->ref]}.pdf");
        $time = microtime(true);
        do {
            if (
                $counter === 0 ||
                Storage::disk('local')->exists($event->layer)
            ) {
                break;
            }
            $counter--;
            usleep(5000);
        } while (true);

        Artisan::call("pdf:layer {$layer} {$background} {$output} {$event->position} {$event->page} {$time}");
        Storage::disk('local')
            ->put(
                "{$event->tmp_dir}/output-layer-added-on-position-{$event->row[$event->ref]}.pdf",
                "finished {$event->row[$event->ref]}"
            );
    }

    /**
     * @param $event
     */
    public function addLayerFiles($event)
    {
        collect($event->assets)->each(function ($storage, $k) use ($event) {
            $time = time() . $k . $event->row[$event->ref];
            $output_path = "{$event->output_path}/output-{$time}-{$event->row[$event->ref]}.pdf";
            $base = Storage::disk('local')->path($event->base);
            $background = Storage::disk($storage['disk'])->path($storage['path']);
            $output = Storage::disk($storage['disk'])->path($output_path);
            $page = optional($storage)['page'];
            Artisan::call("pdf:layer {$background} {$base} {$output} {$storage['position']} {$page}");
            Storage::disk('local')->delete($event->base);
            Storage::disk('local')->move($output_path, $event->base);
        });

        $tmp = Str::replace('output', 'output-tmp', $event->output_path);
        Storage::disk('local')
            ->put(
                "{$tmp}/output-layer-added-{$event->row[$event->ref]}.pdf",
                "finished {$event->row[$event->ref]}"
            );
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function replaceStringOnPdfListener($event)
    {
        match ($event->tool) {
            'pdftool' => $this->replaceStringOnPdfToolListener($event),
            'pdfco' => $this->replaceStringOnPdfCoListener($event),
            default => throw new RuntimeException("No service found with this name {$event->tool}.")
        };
    }


    /**
     * @param $event
     */
    public function mergeFiles($event)
    {
        Artisan::call("pdf:merge {$event->in} {$event->out}");

        $event->queue->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);
    }

    /**
     * @param $event
     */
    public function mergePdfFiles($event)
    {
        $counter = 30;

        $result = app(PdftoolService::class)->mergePdfFiles(
            directory: $event->directory,
            disk: $event->disk,
            destinations: $event->destinations,
            filename: $event->filename,
            separate: $event->separate
        );

        if (optional($result)['status'] === 200) {

            collect(optional($result)['results'])->each(function($obj) use ($event) {
                if(!Storage::disk('local')->exists("{$obj['destinations']}/$event->filename")) {
                    cloneData($obj['disk'], $obj['path'], 'local', "{$obj['destinations']}/$event->filename");

                Storage::disk('local')
                    ->put(
                        "{$event->tmp_output_dir}/output-merge-{$obj['dir']}-{$event->filename}",
                        "finished"
                    );
                }
            });
        }

//        $event->queue->update([
//            'end_at' => Carbon::now(),
//            'await' => false,
//            'busy' => false,
//        ]);
    }

    /**
     * @throws ImagickException
     */
    public function converterPdf($event)
    {
        $base = Storage::disk('local')->path($event->base);
        $to = Storage::disk('local')->path($event->to);
        $geo = new Imagick($base);
        $geo = $geo->getImageGeometry();
        $y = optional($geo)['height'] ? $event->orientation['y'] - optional($geo)['height'] : $event->orientation['y'];

        Artisan::call("pdf:converter {$base} {$to} {$event->orientation['x']} {$y}");
    }

    /**
     * @param $event
     */
    public function separatePdf($event)
    {
        $split = $event->split ? "split" : null;
        Artisan::call('pdf:separate ' . $event->pages . ' ' . $event->in . ' ' . Storage::disk('local')->path($event->out) . ' ' . $split);
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    protected function replaceStringOnPdfToolListener($event): void
    {
        $counter = 30;

        $result = app(PdftoolService::class)->findAndReplaceMultipleStrings(
            search: $event->params['to_replace']['search'],
            replace: $event->params['to_replace']['replace'],
            url: $event->params['to_replace']['url'],
            sync: $event->sync
        );

        if (!$result) {
            throw new RuntimeException("No result from pdf tool");
        }

        if (optional($result)['status'] === 200) {

            do {
                if ($counter === 0 || Storage::disk('local')->exists(Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"))) {
                    $path = Storage::disk('local')->path(Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"));
                    Artisan::call("permission:convert {$path} ");
                    break;
                }

                $counter = $this->getCounter($result, Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"), $counter);
            } while (true);
        }
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    protected function replaceStringOnPdfCoListener($event): void
    {
        $counter = 30;

        $result = app(PdfCoService::class)->findAndReplaceMultipleStrings(
            search: $event->params['to_replace']['search'],
            replace: $event->params['to_replace']['replace'],
            url: $event->params['to_replace']['url'],
            sync: $event->sync
        );


        if (!optional($result)['error']) {

            do {
                if ($counter === 0 || Storage::disk('local')->exists(Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"))) {
                    $path = Storage::disk('local')->path(Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"));
                    Artisan::call("permission:convert {$path} ");
                    break;
                }

                $counter = $this->getCounter($result, Str::replace('//', '/',"{$event->params['output_path']}/output-{$event->k}.pdf"), $counter);
            } while (true);
        }
    }

    /**
     * @param array|null $result
     * @param string     $path
     * @param int        $counter
     * @return int
     */
    protected function getCounter(
        null|array $result,
        string $path,
        int $counter
    ): int
    {
        if (optional($result)['url'] && !Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put(
                $path,
                Http::get($result['url'])->body()
            );
        }
        $counter--;
        usleep(5000);
        return $counter;
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            AddLayerEvent::class, // ReplaceStringOnPdfListener
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@addLayerFiles'
        );

        $events->listen(
            ReplaceStringOnPdfEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@replaceStringOnPdfListener'
        );

        $events->listen(
            RemoveFilesEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@removeFiles'
        );

        $events->listen(
            MergeFilesEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@mergeFiles'
        );

        $events->listen(
            SeparatePdfEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@separatePdf'
        );

        $events->listen(
            ConverterPdfEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@converterPdf'
        );

        $events->listen(
            AddLayerOnPositionEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@addLayerOnPosition'
        );

        $events->listen(
            AddStampOnPositionEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@AddStampOnPosition'
        );

        $events->listen(
            MergePdfEvent::class,
            'App\Listeners\Tenant\Blueprints\BlueprintEventListener@mergePdfFiles'
        );
    }

    /**
     * @param $event
     */
    public function failed($event)
    {
        $this->delete();
        event(new FailedBlueprintRunnerEvent(
                optional($event->item)->queue,
                optional($event)->action,
                optional($event)->step,
                $event,
                optional(optional($event)->request)->get('attachment_destination')
            )
        );
    }
}
