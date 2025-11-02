<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;

class UpdateQuantityAction extends Action implements ActionContractInterface
{
    /**
     * @return mixed|void
     */
    public function handle()
    {
        $this->output = $this->request->get('child') ? $this->from : array_merge([
            'updated' => $this->request->get('attachment_to') !== 'self' ?
                $this->request->get('attachment_destination')->update([
                    'qty' => count($this->dependsOn)
                ]) :
                null,
            "count" => count($this->dependsOn),
        ], $this->from);

    }

}
