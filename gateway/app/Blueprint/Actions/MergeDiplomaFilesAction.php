<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Models\Tenants\CartVariation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MergeDiplomaFilesAction implements BluePrintActionContract
{
    use TrashCollectionTraits, HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $rand = time() . rand(000, 999);
        $res = data_get($request->toArray(), $data['input']['from']);
        $pathInfo = collect($res)->first();
        $dir = DIRECTORY_SEPARATOR . $pathInfo['path'];
        $files = collect($res)->map(function ($p) use ($request, &$dir) {
            $this->addToTrash($request, 'local', $p['path'], $p['name']);
            return cleanName(Storage::disk('local')->path($p['path']) . "/" . $p['name']);
        });

        $output = Storage::disk('local')
            ->path(cleanName($dir . '/' . $rand . '.pdf'));

        $filesNames = collect($res)->pluck('name')->toArray();

        if ($files->count()) {
            sleep(10);
//            $this->createReport('MergeDiplomaFilesAction', $files->toArray(), $request);
            Artisan::call("pdf:merge {$files->implode('~')} {$output}");
            $this->addToTrash($request, 'local', $dir, $rand . '.pdf');
            collect($files)->map(function ($p) use ($cart, $filesNames) {
                Storage::disk('local')->delete(cleanName(Str::replace(Storage::disk('local')->path(''), '', $p)));
                CartVariation::find($cart['cart']->id)->media->whereIn('name', $filesNames)->map(fn($i) => $i->delete());
            });


            $request->merge([
                'MergeDiplomaFilesAction' => [
                    'dirname' => $dir,
                    'name' => $rand . '.pdf',
                    'disk' => 'local',
                    'path' => $dir
                ]
            ]);
        } else {
            throw ValidationException::withMessages([
                'meassage' => __('we can\'t handle this request')
            ]);
        }
    }
}
