<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;

class CollectQueueOutputAction extends Action implements ActionContractInterface
{

    public function handle()
    {
        return $this->output = collect($this->from)
            ->map(fn($queue) => optional(optional(optional($queue)['OutputAction'])['output'])['path'])
            ->toArray();
    }

}
