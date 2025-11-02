<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Blueprint;
use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Foundation\Status\Status;
use Carbon\Carbon;

class ProcessChildren extends Action implements ActionContractInterface
{
    /**
     * hold original product
     * @var $product
     */
    protected mixed $product;

    protected mixed $attachment_to;

    protected mixed $attachment_type;

    protected mixed $attachment_destination;

    protected mixed $upload_files;

    protected  bool $keep_child = false;


    public function handle()
    {
        $this->product = $this->request->product;
        $this->attachment_to = $this->request->attachment_to;
        $this->attachment_type = $this->request->attachment_type;
        $this->attachment_destination = $this->request->attachment_destination;
        $this->upload_files = $this->request->UploadFileAction;

        /**
         * unset upload file action if there
         */
        $this->request->offsetUnset('UploadFileAction');
        /**
         * prepare sub blueprint and process
         */
        $signature = $this->signature;

        $this->keep_child = $this->request->get('child');

        if (optional($this->input)->children && count($this->input->children) > 0) {

            collect($this->input->children)->map(function ($bp) use ($signature) {
                $this->request->merge([
                        'signature' => $signature,
                        'attachment_to' => 'self',
                        'attachment_type' => 'output',
                        'user' => $this->request->user,
                        "has_main" => true,
                        'main_attachment_to' =>  $this->attachment_to,
                        'main_attachment_destination' => $this->attachment_destination,
                        'main_attachment_type' => $this->attachment_type,
                        'child' => true,
                        'attachment_destination' => 'request',
                    ]
                );
                $this->runners[] = (new Blueprint($this->request, session()))->init($this->request, $bp)->queue()->id;
            });
        } else {
            collect($this->request->{$this->request->type}->childrens)->map(function ($product) use ($signature) {
                $this->request->merge([
                        'product' => $product,
                        'sku' => $product->sku,
                        'signature' => $signature,
                        'attachment_to' => 'self',
                        'attachment_type' => 'output',
                        'child' => true,
                        'user' => $this->request->user,
                        'attachment_destination' => 'request',
                        "has_main" => true,
                        'main_attachment_to' =>  $this->attachment_to,
                        'main_attachment_destination' => $this->attachment_destination,
                        'main_attachment_type' => $this->attachment_type,
                    ]
                );

                $this->runners[] = (new Blueprint($this->request, session()))->init($this->request)->queue()->id;
            });
        }


        return $this->output = $this->done();

    }

    /**
     * @return array
     */
    public function done(): array
    {
        do {
            $children = $this->queue->whereIn('id', $this->runners)->get();

            $values = collect($children)->map(function($child) {
                if($child->st === Status::FAILED) {
                    if($this->attachment_to !== 'self') {
                        $this->attachment_destination->update(['st' => Status::FAILED]);
                    }
                    throw new  \RuntimeException("Child item has failed, please try again later.");
                }
                return $child->started;
            })->toArray();

            if (!in_array(true, $values, true)) {
                break;
            }
            usleep(50000);
        } while (true);

        $this->job->update([
            'end_at' => Carbon::now(),
            'await' => false,
            'busy' => false,
        ]);


        sleep(1);

        /** reset request back to original **/
        $this->request->merge([
                'product' => $this->product,
                'sku' => $this->product->sku,
                'signature' => $this->signature,
                'child' => $this->keep_child,
                'attachment_to' => $this->attachment_to,
                'attachment_type' => $this->attachment_type,
                'attachment_destination' => $this->attachment_destination,
                'UploadFileAction' => $this->upload_files
            ]
        );

        return $this->queue->whereIn('id', $this->runners)->pluck('output')->toArray();
    }
}
