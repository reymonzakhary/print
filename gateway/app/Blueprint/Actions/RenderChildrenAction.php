<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Blueprint\Services\BluePrintServices;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Models\Tenants\CartVariation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RenderChildrenAction implements BluePrintActionContract
{
    use HasReportingTraits, TrashCollectionTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $result = [];
        if (optional($request)['MergePdfFilesAction']) {
            $result[] = $request['MergePdfFilesAction'];
        }
        $request->sku->children->map(function ($sku) use ($request, $cart, &$result) {
            $request->merge([
                'product' => $sku->product,
                'sku' => $sku,
                'initBluePrint' => (object)[
                    'hasBlueprint' => $sku->product->hasBlueprint,
                ]
            ]);
            $keys = collect($request)->filter(fn($i) => $i instanceof UploadedFile)->keys()->toArray();
            $request = new CartStoreRequest($request->except($keys));
            app(BluePrintServices::class)->handle($request, $cart['cart'], $cart['cart_id']);

            $data = $request['MergePdfFilesAction'];
//            dump($request['ResultsAction']);
            $re = optional($request['ResultsAction'])['attachments'][0];
            if ($re) {
                CartVariation::where('id', $request['ResultsAction']['cart_variation'])
                    ->first()?->media()
                    ->where('name', $re['name'])
                    ->delete();
                Storage::disk($re['disk'])->delete($re['path'] . '/' . $re['name']);
            }
            //$this->addToTrash($request, $re['disk'], $re['path'], $re['name']);
            unset($request['ResultsAction']);
            $result[] = $data;
        });
//        $this->createReport('Render Children Action', $result,$request);
        unset($request['ResultsAction']);
        $request->merge([
            'RenderChildrenAction' => $result
        ]);
    }
}
