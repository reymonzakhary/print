<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Illuminate\Support\Facades\Storage;

class CollectQueueOutputDirectoryAction extends Action implements ActionContractInterface
{
    /**
     * @return array|mixed
     */
    public function handle()
    {
        return $this->output = collect($this->from)
            ->mapWithKeys(fn($queue) => [
                (string)optional(optional(optional($queue)['OutputAction'])['output'])['product_id'] =>
                    collect(
                        Storage::disk('local')
                            ->allFiles(optional(optional(optional($queue)['OutputAction'])['output'])['dir'])
                    )
                        ->filter(fn($item) => strpos($item, '.pdf'))->toArray()
            ])
            ->toArray();
    }
}
