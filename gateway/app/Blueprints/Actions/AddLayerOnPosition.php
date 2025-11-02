<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Validations\Validator;
use App\Events\Tenant\Blueprints\AddStampOnPositionEvent;
use App\Events\Tenant\Blueprints\RemoveFilesEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AddLayerOnPosition extends Action implements ActionContractInterface
{

    /**
     * @return void
     */
    public function handle()
    {
        $this->output = [];
        collect($this->from)->each(function ($value) {
            $this->assets = $this->getUses($value);
            collect($this->dependsOn[$value[$this->ref]])->each(function ($pos) use ($value) {
                $assets = $this->assets[$this->dependsOnConfig->replace[collect($this->dependsOnConfig->search)->search($pos['key'])]];

                $original_path = Storage::disk('local')->url(Str::replace('//', '/', $pos['path']));

                if (env('APP_ENV') === 'local') {
                    if (!Storage::disk('carts')->exists(Str::replace('//', '/', $pos['path']))) {
                        cloneData(
                            'local', Str::replace('//', '/', $pos['path']),
                            'carts', Str::replace('//', '/', $pos['path'])
                        );
                    }

                    $original_path = Storage::disk('carts')->url(Str::replace('//', '/', $pos['path']));

                }

                event(new AddStampOnPositionEvent(
                    $this->request->get('attachment_destination'),
                    'ConverterPdfEvent',
                    $this->id,
                    $original_path, // original path
                    $assets['original_url'], // stamp path
                    (float)$pos['x'], // x position
                    (float)$pos['y'],//$y, // y position
                    (int)$pos['page'],
                    $value,
                    $this->ref,
                    $this->output_path,
                    $this->tmp_output_dir,
                    $pos['path'],
                    $pos['key'],
                    0 > $pos['ft']
                ));
            });
        });

        $this->output = $this->done();
    }

    /**
     * @param array $row
     * @return mixed
     */
    protected function getUses(
        array $row = [],
    ): mixed
    {
        return collect($this->uses)->map(fn($uses) => match ($uses) {
            'assets' => $this->loadAssets($this->pipeline->{$uses}, $row)
        })->first();
    }

    /**
     * @param $collection
     * @param $row
     * @return array
     */
    private function loadAssets($collection, $row): array
    {
        $assets = [];
        collect($collection)->each(function ($obj) use (&$assets, $row) {
            $name = Validator::HasValueFromRegExp($obj->name, "/\[(.*?)]/", 'AddLayerOnPositionAction', $row);
            $path = cleanName("{$this->signature}/assets/{$name}");

            /**
             *
             */
            if (!Storage::disk($obj->disk)->exists("{$this->request->tenant->uuid}/{$obj->path}{$name}")) {
                throw new RuntimeException("File {$obj->path}{$name} does not exist");
            }

            if (!Storage::disk('local')->exists($path)) {
                cloneData($obj->disk, "{$this->request->tenant->uuid}/{$obj->path}{$name}", 'local', $path);
            }

            $assets[$obj->as] = [
                'as' => $obj->as,
                'name' => $name,
                'disk' => 'local',
                'path' => $path,
                'url' => Storage::disk('local')->url($path),
                'position' => $obj->position,
                'origin_disk' => $obj->disk,
                'origin_path' => "{$this->request->tenant->uuid}/{$obj->path}{$name}",
                'original_url' => Storage::disk($obj->disk)->url("{$this->request->tenant->uuid}/{$obj->path}{$name}"),
            ];
        })->toArray();

        return $assets;
    }

    /**
     * @return array
     */
    protected function done(): array
    {

        $done = collect($this->from)
            ->map(fn($v, $k) => "{$this->tmp_output_dir}/output-layer-added-on-position-{$v[$this->ref]}.pdf")
            ->toArray();

        do {
            $values = collect($done)->map(fn($path) => Storage::disk('local')->exists($path))->toArray();
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

        event(new RemoveFilesEvent(
            $this->request->get('attachment_destination'),
            'RemoveFilesEvent ' . __CLASS__,
            $this->id,
            'local',
            $done
        ));
        $output = [];

        collect($this->from)->each(function ($value)  use (&$output) {
            collect($this->dependsOn[$value[$this->ref]])->each(function ($v,$k)  use (&$output, $value) {
                $output[$value[$this->ref]] = Str::replace('//', '/', $v['path']);
            });
        });

        return $output;
    }
}
