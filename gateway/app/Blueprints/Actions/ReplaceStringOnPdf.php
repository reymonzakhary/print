<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Validations\Validator;
use App\Events\Tenant\Blueprints\ReplaceStringOnPdfEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplaceStringOnPdf extends Action implements ActionContractInterface
{
    /**
     * @return void
     */
    public function handle(): void
    {
        collect($this->from)->each(function ($v, $k) {
            if (optional($this->input)->file_name) {
                $row = collect(...array_map(fn($c) => data_get($this->request->all(), $c), $this->input->uses))->firstWhere($this->input->ref, $k);
                $k = Validator::HasValueFromRegExp(optional($this->input)->file_name, "/\[(.*?)]/", 'MatchStringWithReplace', is_array($row)?$row:[]);
            }
            event(new ReplaceStringOnPdfEvent(
                $this->request->get('attachment_destination'),
                'ReplaceStringOnPdfEvent',
                $this->id,
                $v,
                false,
                "{$this->signature}_{$k}",
                $k,
                Str::lower($this->tool)
            ));
        });

        $this->output = $this->download();
    }

    /**
     * @return array
     */
    protected function download(): array
    {
        $done = collect($this->from)->map(function ($v, $k) {
            if (optional($this->input)->file_name) {
                $row = collect(...array_map(fn($c) => data_get($this->request->all(), $c), $this->input->uses))->firstWhere($this->input->ref, $k);
                $k = Validator::HasValueFromRegExp(optional($this->input)->file_name, "/\[(.*?)]/", 'MatchStringWithReplace', is_array($row)?$row:[]);
            }
            return "{$v['output_path']}/output-{$k}.pdf";
        });

        do {
            $values = $done->map(fn($path) => Storage::disk('local')->exists($path))->toArray();
            if (!in_array(false, $values, true)) {
                break;
            }
            usleep(500);
        } while (true);
        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);
        return $done->toArray();
    }
}
