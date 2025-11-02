<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Events\Tenant\Blueprints\SeparatePdfEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SeparatePdfAction extends Action implements ActionContractInterface
{

    /**
     * @return array
     */
    public function handle()
    {
        collect($this->from['collection'])->map(function ($v, $k) {
            $pages = collect($v)->pluck('pageCount')->implode('~');
            $output = "{$this->template_path}/output_{$k}";
            if(!Storage::disk('local')->exists("{$output}.pdf")) {
                event(new SeparatePdfEvent(
                    $this->request->get('attachment_destination'),
                    'SeparatePdfEvent',
                    $this->id,
                    $pages,
                    $this->from['path'],
                    $output
                ));
            }

        });

        return $this->output = $this->done();

    }


    /**
     * @return array
     */
    protected function done(): array
    {

        $done = collect($this->from['collection'])
            ->map(fn($v, $k) => "{$this->template_path}/output_{$k}.pdf")
            ->toArray();

//        do {
//            $values = collect($done)->map(fn($path) => Storage::disk('local')->exists($path))->toArray();
//            if (!in_array(false, $values, true)) {
//                break;
//            }
//            usleep(5000);
//        } while (true);

//        sleep(4);
        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);

        return $done;
    }

}
