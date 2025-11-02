<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Events\Tenant\Blueprints\MergePdfEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OrganizeFilesAction extends Action implements ActionContractInterface
{

    /**
     * @return array
     */
    public function handle(): array
    {
        /**
         * sort files
         * picking list
         */
        $collection = [];
        $output = [];

        collect($this->dependsOn)->each(function ($row) use (&$collection) {
            collect($this->input->orderby)->map(function ($o) use ($row, &$collection) {
                $collection[$row[$this->ref]][] = collect(optional($this->from)[$o])->filter(fn($path) => Str::contains($path, $row[$this->ref]))
                    ->sortBy(fn ($item) =>
                        Str::contains( $item,'copy' ) ? 1 : 0
                    )->values();
            });

            $collection[$row[$this->ref]] = collect($collection[$row[$this->ref]])->flatten(1)->toArray();
        });

        $dir = Str::random(8);
        collect($collection)->each(function ($rows, $student) use (&$output, $dir) {
            collect($rows)->each(function ($path, $index) use ($student, $dir) {
                $list =  explode('/', $path);
                array_pop($list);
                $k = last($list);
                cloneData('local', $path, 'carts', "merged/{$student}/{$this->signature}/{$dir}/{$index}_{$k}_{$student}_output.pdf");
            });

            $rand = Str::random(4);
            $time = Carbon::now()->format('Y-m-d_h-i-s');
            $a4_directory = "{$this->signature}/output/{$this->request->product->slug}/A4";
            $a3_directory = "{$this->signature}/output/{$this->request->product->slug}/A3";
            $filename = "{$rand}-{$student}-{$time}-output.pdf";
            /**
             * make dir if not exists
             */
            if (!Storage::disk('local')->exists($a4_directory)) {
                Storage::disk('local')->makeDirectory($a4_directory);
            }
            /**
             * make dir if not exists
             */
            if (!Storage::disk('local')->exists($a3_directory)) {
                Storage::disk('local')->makeDirectory($a3_directory);
            }
            $output[] = [
                "name" => $filename,
                "directory" => $this->output_path,
            ];
            event(new MergePdfEvent(
                $this->request->get('attachment_destination'),
                'MergeFilesEvent',
                $this->id,
                $this->job,
                'carts',
                "merged/{$student}/{$this->signature}/{$dir}",
                $this->output_path,
                $filename,
                true,
                $this->tmp_output_dir
            ));
        });

        return $this->output = $this->done($output);
    }



    /**
     * @param $output
     * @return array
     */
    protected function done($output): array
    {
        $count = count($output);
        do {
            $ex = [];
            collect($output)->each(function ($path) use ($count, &$ex, &$close) {
                if(Storage::disk('local')->exists("{$path['directory']}A4/{$path['name']}")) {
                    $ex[] = "{$path['directory']}A4/{$path['name']}";
                }elseif(Storage::disk('local')->exists("{$path['directory']}A3/{$path['name']}")) {
                    $ex[] = "{$path['directory']}A3/{$path['name']}";
                }
            });
            if ($count === count($ex)) {
                break;
            }
            usleep(5000);
        } while (true);

        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);

        return $output;
    }
}
