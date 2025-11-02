<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Validations\Validator;
use App\Events\Tenant\Blueprints\RemoveFilesEvent;
use App\Services\Pdftool\PdftoolService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Carbon\Carbon;

class AddDynamicLayerAction  extends Action implements ActionContractInterface
{

    /**
     * @return void
     */
    public function handle(): void
    {
        $assets = [];
        $output = [];
        $s3output = [];
        /**
         * check if validated key has been sent it otherwise get default
         */
        if ($validate = $this->validate) {
            $method = Str::before($validate, ':');
            $from = Str::after($validate, ':');
            $data = Validator::{$method}(
                request: data_get($this->request->all(), $from),
                column: Str::after($this->input->from, '.')
            );
        } else {
            $data = $this->from;
        }

        /**
         * upload files
         */
        $tmp_dir = Str::random(8);
        collect($data)->each(function ($row) use (&$assets, &$output, &$s3output, $tmp_dir) {
            $path = Str::replace('//', '/',$this->dependsOn[$row[$this->ref]]);
            $s3path =  $tmp_dir.DIRECTORY_SEPARATOR.$path;
            $output[] = $path;
            $s3output[] = $s3path;
            $template_name = Str::replace($this->output_path,'', $path);
            $assets[] = $this->getUses($row, true, $template_name);
            if(!Storage::disk('carts')->exists($s3path)) {
                cloneData('local', $path, 'carts', $s3path);
            }
            Storage::disk('local')->delete($path);
        });

        $result = app(PdftoolService::class)->addDynamicLayer(
            layers: $assets,
            templates: $tmp_dir.DIRECTORY_SEPARATOR.$this->output_path,
            destination:  $tmp_dir.DIRECTORY_SEPARATOR.$this->output_path,
            disk: 'carts'
        );

        if(optional($result)['status'] === 200) {
            collect($s3output)->each(function ($path) {
                $s3path = $path;
                $arr = explode('/', $path);
                array_shift($arr);
                $path = implode('/', $arr);
                cloneData('carts', $s3path, 'local', $path);
            });
        }

        $this->output = $this->done($output, $s3output);
    }

    protected function getUses(
        $row = [],
        $cloud = false,
        $template_name = null
    )
    {
        return collect($this->uses)->map(fn($uses) => match ($uses) {
            'assets' => $this->loadAssets($this->pipeline->{$uses}, $row, $cloud,$template_name)
        })->first();
    }

    /**
     * @param             $collection
     * @param             $row
     * @param bool        $cloud
     * @param string|null $template_name
     * @return array
     */
    private function loadAssets(
        $collection,
        $row,
        bool $cloud = false,
        null|string $template_name = null
    ): array
    {
        $assets = [];
        if (empty($row)) {
            return $collection;
        }
        collect($collection)->each(function ($obj) use (&$assets, $row, $cloud,$template_name) {
            $name = Validator::HasValueFromRegExp($obj->name, "/\[(.*?)]/", 'AddLayerAction', $row);
            $obj->path = Validator::HasValueFromRegExp($obj->path, "/\[(.*?)]/", 'AddLayerAction', $row);
            $path = cleanName("{$this->signature}/assets/{$row[$this->ref]}-{$name}");

            /**
             *
             */
            if (!Storage::disk($obj->disk)->exists("{$this->request->tenant->uuid}/{$obj->path}{$name}")) {
                throw new RuntimeException("File {$obj->path}{$name} does not exist.");
            }

            if (!$cloud && !Storage::disk('local')->exists($path)) {
                cloneData($obj->disk, "{$this->request->tenant->uuid}/{$obj->path}{$name}", 'local', $path);
            }

            $assets[] = [
                "ref" => optional($obj)->ref,
                "page" => optional($obj)->page,
                "uses" => optional($obj)->uses,
                "regex" => optional($obj)->regex,
                "fallback" => optional($obj)->fallback,
                'disk' => $cloud?optional($obj)->disk:'local',
                'path' => $cloud?tenant()->uuid.'/'.optional($obj)->path.$name:$path,
                'position' => $obj->position,
                'template_name' => $template_name
            ];
        })->toArray();
        return $assets;
    }

    /**
     * @param array $paths
     * @param array $s3paths
     * @return array
     */
    protected function done(
        array $paths = [],
        array $s3paths = []
    ): array
    {
        do {
            $values = collect($paths)->map(fn($path) => Storage::disk('local')->exists($path))->toArray();
            if (!in_array(false, $values, true)) {
                break;
            }
            usleep(5000);
        } while (true);

        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);

        event(new RemoveFilesEvent($this->request->get('attachment_destination'),
            'RemoveFilesEvent',
            $this->id, 'carts', $s3paths));
        return $this->dependsOn;
    }
}
