<?php

namespace App\Blueprint\Processors;

use App\Models\Tenant\Cart;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MergePdfFilesAction
{
    protected $filesPath;
    protected $fullpath;

    public function handle($request)
    {

        return $this->download($request)->merge($request);

    }

    public function merge()
    {
        $rand = time();
        $files = collect($this->filesPath)->map(function ($p) {
            return Storage::disk('tenancy')->path($this->fullpath) . "/" . $p['name'];
        });
        $output = Storage::disk('tenancy')
            ->path($this->fullpath . '/' . $rand . '.pdf');
        /**
         * @todo fix response shell
         */
        Artisan::call("pdf:merge {$files->implode('~')} {$output}");
        collect($files)->map(function ($p) {
            Storage::disk('tenancy')->delete(Str::replace(Storage::disk('tenancy')->path(''), '', $p));
        });

        return [
            'dirname' => $this->fullpath,
            'name' => $rand . '.pdf'
        ];
    }

    public function download($request, $path = "converter")
    {
        $cart = Cart::whereUuid(session(tenant()->uuid . '_cart_session'))->first();
        $this->fullpath = tenant()->uuid . '/' . $cart->id . '/' . $request['product']->id . '/' . $request['sku']->id;
        $this->filesPath = collect($request['result'])->map(function ($i) use ($path) {
            Storage::disk('tenancy')->put($this->fullpath . '/' . $i['name'], Http::get($i['url'])->getBody());
            return [
                'name' => $i['name'],
            ];
        });
        return $this;
    }
}
