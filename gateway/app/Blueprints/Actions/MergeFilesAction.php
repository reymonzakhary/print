<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Events\Tenant\Blueprints\MergeFilesEvent;
use App\Events\Tenant\Blueprints\MergePdfEvent;
use App\Events\Tenant\Blueprints\RemoveFilesEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MergeFilesAction extends Action implements ActionContractInterface
{

    public function handle()
    {

        $this->from = match (optional($this->input)->base) {
            'request' => $this->getFromRequest(),
            'parent' => $this->getFromParent(),
            default => $this->from
        };

        $directory = Str::random(8);
        collect($this->from)->map(fn($path) => cloneData('local', $path, 'carts', "{$directory}/{$path}"));
        $rand = Str::random(4);
        $output = "{$this->output_path}/{$rand}-output.pdf";

        event(new MergePdfEvent(
            $this->request->get('attachment_destination'),
            'MergeFilesEvent',
            $this->id,
            $this->job,
            'carts',
            "{$directory}/{$this->output_path}",
            $this->output_path,
            "{$rand}-output.pdf",
            false,
            $this->tmp_output_dir
        ));

        return $this->output = $this->done($output, $directory);
    }

    /**
     * @return array
     */
    protected function getFromRequest(): array
    {
        $this->from = array_merge($this->from, data_get(collect($this->dependsOn)->first(), optional($this->input)->keys));
        if ($this->input?->orderBy) {
            $this->from = collect(
                data_get($this->request->toArray(), $this->input?->orderBy)
            )->flatMap(fn($row) => collect($this->from)->filter(fn($f) => Str::contains($f, $row[$this->ref])
            )
            )->toArray();
        }
        return $this->from;
    }

    /**
     * @return array
     */
    protected function getFromParent(): array
    {
        if (optional($this->input)->keys) {
            $this->from = collect(explode(',', $this->input->keys))->map(fn($k) => $this->from[$k])->flatten()->toArray();
            if ($this->input?->orderBy) {
                $this->from = collect(
                    data_get($this->request->toArray(), $this->input?->orderBy)
                )->flatMap(fn($row) => collect($this->from)->filter(
                        fn($f) => Str::contains($f, $row[$this->ref])
                    )
                )->toArray();
            }
        }
        return $this->from;
    }


    /**
     * @param string $output
     * @param string $directory
     * @return array
     */
    protected function done(
        string $output,
        string $directory
    ): array
    {
        do {
            if (Storage::disk('local')->exists($output)) {
                break;
            }
            usleep(50000);
        } while (true);

        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);

        event(new RemoveFilesEvent($this->request->get('attachment_destination'),
            'RemoveFilesEvent',
            $this->id, 'carts', Storage::disk('carts')->allFiles($directory)));

        return [
            "path" => $output,
            "url" => Storage::disk('local')->url($output),
            "disk" => "local"
        ];
    }
}
