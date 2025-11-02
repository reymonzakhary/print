<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OutputAction extends Action implements ActionContractInterface
{
    /**
     * @throws ValidationException
     */
    public function handle()
    {
        if (optional(optional($this->input)->config)->output) {

            $output = collect(
                array_merge(...collect(
                    explode(',', optional($this->input)->config->output)
                )->map(fn($key) => optional($this->from)[$key])->toArray())
            );

            $output = match (optional($this->input)->config->as) {
                'array' => $output->toArray(),
                'first' => $output->first(),
            };


            return $this->output = $output;
        }
        $disk = match ($this->request->ns) {
            'cart', 'shop' => 'carts',
            'checkout' => 'tenancy'
        };

        $file = Str::after($this->from['path'], $this->request->product?->slug . '/' . $this->request->get('override_path') ? $this->request->get('override_path') . '/' : null);

        $path = "{$this->request->tenant->uuid}/{$this->from['path']}";

        if ($this->request->get('attachment_to') !== 'self') {
            if($this->request->ns === 'checkout') {
                $path = $this->request->get('has_main')?
                    "{$this->request->tenant->uuid}/orders/{$this->request->get('main_attachment_destination')->order->first()->id}/items/{$this->request->get('main_attachment_destination')->id}/{$file}" :
                    "{$this->request->tenant->uuid}/orders/{$this->request->get('attachment_destination')->order->first()->id}/items/{$this->request->get('attachment_destination')->id}/{$file}";
            }
            if(in_array($this->request->ns, ['cart', 'shop'], true)) {
                $path = $this->request->get('has_main') ?
                    "{$this->request->tenant->uuid}/{$this->request->get('main_attachment_destination')->id}/{$this->from['path']}":
                    "{$this->request->tenant->uuid}/{$this->request->get('attachment_destination')->id}/{$this->from['path']}";
            }
        }
        //@todo have to be handled from other location

//        $storage_path = $this->request->get('attachment_to') === 'self' ? $this->from['path'] :
//            "{$this->request->get('attachment_destination')->id}/{$this->from['path']}";


        do {
            if ($this->maximum_loop_count === 0 || Storage::disk('local')->exists($this->from['path'])) {
                break;
            }
            $this->maximum_loop_count--;
            usleep(5000);
        } while (true);

        $path = Str::replace('//', '/', $path);
        cloneData(
            $this->from['disk'],
            $this->from['path'],
            $disk,
            $path
        );
        $output['product'] = $this->product?->slug;
        $output['product_id'] = $this->product->row_id;
        $output['category'] = $this->category?->slug;
        $output['category_id'] = $this->category->row_id;
        $output['storage_path'] = $path;
        $output['storage_disk'] = $disk;

        $output['path'] = Str::replace('//', '/', $this->from['path']);
        $output['url'] = Storage::disk($disk)->url($path);
        $output['disk'] = 'local';
        $output['dir'] = Str::replace('//', '/', $this->output_path);
        return $this->output = $output;
    }
}
