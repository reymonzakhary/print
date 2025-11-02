<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Models\Tenants\Cart;
use App\Models\Tenants\CartVariation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MergePdfFilesAction implements BluePrintActionContract
{

    use TrashCollectionTraits, HasReportingTraits;

    protected $filesPath;
    protected $fullpath;
    private mixed $cart;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $this->cart = $cart;
        return $this->download($request, $data)->merge($request);
    }

    public function merge($request)
    {
        $rand = time();
        $files = collect($this->filesPath)
            ->map(fn($p) => cleanName(Storage::disk('local')->path($this->fullpath) . "/" . $p['name']));

        $output = Storage::disk('local')
            ->path(cleanName($this->fullpath . '/' . $rand . '.pdf'));

        //$this->addToTrash($request, 'local', $this->fullpath, $rand . '.pdf');
        /**
         * @todo fix response shell
         */

        if ($files->count()) {
            $filesNames = collect($this->filesPath)->pluck('name')->toArray();
            Artisan::call("pdf:merge {$files->implode('~')} {$output}");
            sleep(1);
            collect($files)->map(function ($p) use ($filesNames) {
                Storage::disk('local')->delete(cleanName(Str::replace(Storage::disk('local')->path(''), '', $p)));
                CartVariation::find($this->cart['cart']->id)->media->whereIn('name', $filesNames)->map(fn($i) => $i->delete());
            });
//            $this->createReport('Merge Pdf Files Action', $files->toArray(), $request);
            $request->merge([
                'MergePdfFilesAction' => [
                    'dirname' => $this->fullpath,
                    'name' => $rand . '.pdf',
                    'disk' => 'local',
                    'path' => $this->fullpath
                ]
            ]);
        } else {
            throw ValidationException::withMessages([
                'meassage' => __('we can\'t handle this request')
            ]);
        }


    }

    public function download($request, $data, $path = "converter")
    {
        $files = data_get($request->toArray(), $data['input']['from']);

        $cart = Cart::whereUuid($this->cart['cart_id'])->first();
        $this->fullpath = cleanName($request->tenant->uuid . '/' . $cart->id . '/' . $request['product']->id . '/' . $request['sku']->id);
        $this->filesPath = collect($files)->map(function ($i) use ($path, $request) {
            if (optional($i)['url']) {
                $rand = time() . rand(0000, 1111) . '.pdf';
                //usleep(500000);
                do {
                    Storage::disk('local')->put(cleanName($this->fullpath . '/' . $rand), Http::get($i['url'])->getBody());

                    if (Storage::disk('local')->exists(cleanName($this->fullpath . '/' . $rand))) {
                        break;
                    }
                    usleep(50000);
                } while (true);
                //$this->addToTrash($request, 'local', $this->fullpath, $rand);

                return [
                    'name' => $rand,
                ];
            }
        })->filter();
        return $this;
    }


}
