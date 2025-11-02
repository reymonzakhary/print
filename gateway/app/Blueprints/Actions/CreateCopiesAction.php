<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Processors\MatchStringWithReplace;
use App\Events\Tenant\Blueprints\AddLayerEvent;
use App\Events\Tenant\Blueprints\RemoveFilesEvent;
use App\Services\PdfCo\PdfCoService;
use App\Services\Pdftool\PdftoolService;
use Carbon\Carbon;
use \RuntimeException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateCopiesAction extends Action implements ActionContractInterface
{

    /**
     * @return mixed|void
     * {
     * "id": 9,
     * "uses": "action",
     * "event": {},
     * "action": {
     * "as": "copies",
     * "file": false,
     * "input": {
     * "from": "MergeFilesAction.merged",
     * "uses": [
     * "assets"
     * ],
     * "config": {
     * "xnum": 3
     * },
     * "validate": "xlsx:ExtractExcelDataAction.rows",
     * "dependsOn": "ExtractExcelDataAction.rows"
     * },
     * "model": "CreateCopiesAction"
     * },
     * "active": true,
     * "assets": [
     * {
     * "disk": "tenancy",
     * "name": "Certificaat_FPN_Cert-Copy.pdf",
     * "path": "Assets/FPN/Certificaat/certified_copy/",
     * "type": "storage",
     * "regex": false,
     * "position": "background"
     * }
     * ],
     * "decision": false,
     * "queueable": true,
     * "transition": {}
     * },
     *
     */
    public function handle()
    {
        $copies = [];
        /**
         * check what we receive from data
         */
        $this->output = match ($this->input->config?->multiple) {
            true => $this->copyMultipleFiles($copies),
            false => $this->copySingleFile($copies),
        };
    }

    /**
     * @param $nums
     * @return array
     */
    protected function copyMultipleFiles(
        $nums
    ): array
    {
        $copies = $nums;
        collect($this->from)->each(function ($file, $key) use (&$copies) {
            collect(range(1, $this->input->config->xnum))->each(function ($r) use (&$copies, $file, $key) {
                if (!Storage::disk('local')->exists("{$this->output_path}/copy-{$r}-{$key}-output.pdf")) {
                    $path = Storage::disk(optional($this->from)['disk'] ?? 'local')->copy(
                        $file,
                        "{$this->output_path}copy-{$r}-{$key}-output.pdf"
                    );
                }
                $copies['copies'][] = "{$this->output_path}copy-{$r}-{$key}-output.pdf";
                event(new AddLayerEvent(
                    $this->request->get('attachment_destination'),
                    'AddLayerEvent',
                    $this->id,
                    $this->getUses($key),
                    ['name' => "{$r}-{$key}-copy"],
                    'name',
                    "{$this->output_path}copy-{$r}-{$key}-output.pdf",
                    $this->output_path
                ));
            });
        });

        return $this->output = array_merge($this->done(), $copies);
    }

    /**
     * @param $nums
     * @return array
     */
    protected function copySingleFile(
        $nums
    ): array
    {
        $copies = $nums;
        collect(range(1, $this->input->config->xnum))->each(function ($r) use (&$copies) {
            if (!Storage::disk('local')->exists("{$this->output_path}/copy-{$r}-output.pdf")) {
                $path = Storage::disk($this->from['disk'])->copy(
                    $this->from['path'],
                    "{$this->output_path}/copy-{$r}-output.pdf"
                );
            }
            $copies['copies'][] = "{$this->output_path}/copy-{$r}-output.pdf";
            event(new AddLayerEvent(
                $this->request->get('attachment_destination'),
                'AddLayerEvent',
                $this->id,
                $this->getUses(),
                ['name' => "{$r}-copy"],
                'name',
                "{$this->output_path}/copy-{$r}-output.pdf",
                $this->output_path
            ));
        });

        return $this->output = array_merge($this->done(), $copies);
    }

    /**
     * @return mixed
     */
    protected function getUses(
        string $key = null
    ): mixed
    {
        return collect($this->uses)->map(fn($uses) => match ($uses) {
            'assets' => $this->loadAssets($this->pipeline->{$uses}, $key)
        })->first();
    }

    /**
     * @param $collection
     * @return array
     */
    private function loadAssets(
        $collection,
        ?string $key = null
    ): array
    {
        $assets = [];
        collect($collection)->each(function ($obj) use (&$assets, $key) {

            $path = cleanName("{$this->signature}/assets/{$obj->name}");

            /**
             *
             */
            if (!Storage::disk($obj->disk)->exists("{$this->request->tenant->uuid}/{$obj->path}/{$obj->name}")) {
                throw new \RuntimeException("On $obj->name, file {$obj->path}{$obj->name} does not exist.");
            }

            if (!Storage::disk('local')->exists($path)) {
                cloneData($obj->disk, "{$this->request->tenant->uuid}/{$obj->path}{$obj->name}", 'local', $path);
            }

            if(optional($obj)->replace) {
                $row = collect(data_get($this->request->all(),  $obj->replace->use))->firstWhere($obj->replace->ref, $key);
                $replace = collect($obj->replace->replace)->map(fn($text) => MatchStringWithReplace::single('/\[(.*?)\]/i', $text, $row))
                    ->first();
                if($row) {
                    $assets_name = basename($path);

                    $assets_path = "{$this->assets_tmp_path}/{$key}_{$assets_name}";
                    if(!Storage::disk('local')->exists($assets_path)) {
                        cloneData('local', $path, 'carts', $assets_path);
                        $path = "{$this->assets_path}/{$key}_{$assets_name}";
                        match($obj->replace->tool) {
                            "pdfco" => $this->findAndReplacePdfCo(
                                $assets_path,
                                $replace,
                                Storage::disk('carts')->url($assets_path),
                                $path
                            ),
                            default => $this->findAndReplacePdfTool(
                                $assets_path,
                                $replace,
                                Storage::disk('carts')->url($assets_path),
                                $path
                            ),
                        };
                    }
                    $path = "{$this->assets_path}/{$key}_{$assets_name}";
                }
            }

            $assets[] = [
                'disk' => 'local',
                'path' => $path,
                'position' => $obj->position
            ];
        })->toArray();

        return $assets;
    }

    /**
     * @return array
     */
    protected function done(): array
    {

        $output_path = "{$this->signature}/output-tmp/{$this->request->product->slug}";
        $done = match ($this->input->config?->multiple) {
            true => collect(range(1, $this->input->config->xnum))
                ->flatMap(fn($r) => collect(array_keys($this->from))->map(fn($k) => "{$output_path}/output-layer-added-{$r}-{$k}-copy.pdf"))
                ->toArray(),
            false => collect(range(1, $this->input->config->xnum))
                ->map(fn($r) => "{$output_path}/output-layer-added-{$r}-copy.pdf")
                ->toArray()
        };

        do {
            $values = collect($done)->map(fn($path) => Storage::disk('local')->exists($path))->toArray();
            if (!in_array(false, $values, true)) {
                break;
            }
        } while (true);

        event(new RemoveFilesEvent(
            $this->request->get('attachment_destination'),
            'RemoveFilesEvent ' . __CLASS__,
            $this->id,
            'local',
            $done
        ));
        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);
        return optional($this->input->config)->multiple ? ['path' => $this->from] : $this->from;
    }

    /**
     * @param string $path
     * @param array  $event
     * @param string $url
     * @param string $original_path
     */
    private function findAndReplacePdfTool(
        string $path,
        array $event,
        string $url,
        string $original_path
    ): void
    {
        $counter = 30;

        $result = app(PdftoolService::class)->findAndReplaceMultipleStrings(
            search: $event['search'],
            replace: $event['replace'],
            url: $url
        );

        if (!$result) {
            throw new RuntimeException("No result from pdf tool");
        }

        if (optional($result)['status'] === 200) {

            do {
                if ($counter === 0 || Storage::disk('local')->exists($path)) {
                    $path = Storage::disk('local')->path($path);
                    Artisan::call("permission:convert {$path} ");
                    break;
                }

                if (optional($result)['url'] && !Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->put(
                        $path,
                        Http::get($result['url'])->body()
                    );
                }
                $counter--;
                usleep(5000);
            } while (true);
            $this->download($path, $original_path);
        }
    }

    /**
     * @param string $path
     * @param array  $event
     * @param string $url
     * @param string $original_path
     */
    private function findAndReplacePdfCo(
        string $path,
        array $event,
        string $url,
        string $original_path
    ): void
    {
        $counter = 30;

        $result = app(PdfCoService::class)->findAndReplaceMultipleStrings(
            search: $event['search'],
            replace: $event['replace'],
            url: $url,
            sync: $event->sync
        );

        if (!$result) {
            throw new RuntimeException("No result from pdf tool");
        }

        if (optional($result)['status'] === 200) {

            do {
                if ($counter === 0 || Storage::disk('local')->exists($path)) {
                    $path = Storage::disk('local')->path($path);
                    Artisan::call("permission:convert {$path} ");
                    break;
                }

                if (optional($result)['url'] && !Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->put(
                        $path,
                        Http::get($result['url'])->body()
                    );
                }
                $counter--;
                usleep(5000);
            } while (true);

            $this->download($path, $original_path);
        }

    }

    private function download(
        $path,
        $original_path
    )
    {
        $counter = 30;

        $path = Str::replace(storage_path('app/public/'), '', $path);
        $original_path = Str::replace(storage_path('app/public/'), '', $original_path);

        do {
            if($counter === 0 || Storage::disk('local')->exists($path)) {
                break;
            }

            $counter--;
            usleep(5000);
        }
        while (true);
//        Storage::disk('local')->delete($original_path);
        Storage::disk('local')->copy($path, $original_path);
        Storage::disk('carts')->delete($path);
    }
}
