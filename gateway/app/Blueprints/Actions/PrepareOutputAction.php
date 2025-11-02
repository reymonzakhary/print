<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Illuminate\Support\Facades\Storage;

class PrepareOutputAction extends Action implements ActionContractInterface
{

    public function handle()
    {
        $path = collect($this->from)->first();
        $this->output = [
            'path' => $path,
            'url' => Storage::disk('local')->url($path),
            'disk' => 'local',
        ];
    }
}
