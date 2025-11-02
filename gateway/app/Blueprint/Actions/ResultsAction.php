<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\MediaHandlerTrait;
use App\Blueprint\Contract\BluePrintActionContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResultsAction implements BluePrintActionContract
{
    use MediaHandlerTrait, HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $collection = optional($request)->resolution === "production" ? 'production' : 'preview';
        $res = data_get($request->toArray(), $data['input']['from']);
        $path = Storage::disk("local")
            ->path($res['dirname']);
        $fileDetails = new UploadedFile($path . DIRECTORY_SEPARATOR . $res['name'], $res['name']);
        $this->addMedia(
            $cart['cart'],
            $fileDetails,
            Str::replace($request->tenant->uuid . DIRECTORY_SEPARATOR, '', $res['dirname']),
            $collection . "-" . $request['sku']->id,
            'carts',
            $request->tenant->uuid,
            $request->tenant->user->id
        );

        $fileInfo = [
            'disk' => 'carts',
            'name' => cleanName($fileDetails->getClientOriginalName()),
            'path_tenant' => $res['dirname'],
            'path' => cleanName(Str::replace($request->tenant->uuid . '/', '', $res['dirname'])),
            'ext' => $fileDetails->getExtension(),
            'size' => $fileDetails->getSize(),
            'group' => $fileDetails->getExtension(),
            'collection' => $collection . "-" . $request['sku']->id,
            'type' => $fileDetails->getMimeType()
        ];
//        $this->createReport('Results', $fileInfo,$request);
        $request->merge([
            'ResultsAction' => [
                'cart_variation' => $cart['cart']->id,
                'status' => $cart['cart']->status,
                'attachments' => [$fileInfo],
                'quantity' => $request['quantity'],
                'product' => $request['product'],
            ]
        ]);
    }
}
