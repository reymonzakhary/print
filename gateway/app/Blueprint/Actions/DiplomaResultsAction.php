<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Models\Tenant\CartVariation;
use App\Models\Tenant\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiplomaResultsAction implements BluePrintActionContract
{
    use HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $collection = optional($request)->resolution === "production" ? 'production' : 'preview';
        $restult = data_get($request->toArray(), $data['input']['from']);
        $files = collect($restult)->map(function ($res) use ($cart, $request, $collection) {


            $CartVariation = CartVariation::find($cart['cart']->id);
            $path = Storage::disk("local")
                ->path($res['dirname'] . '/' . $res['name']);
            $fileDetails = new UploadedFile($path, $res['name']);

            cloneData(
                'local',
                cleanName($res['dirname'] . DIRECTORY_SEPARATOR . $res['name']),
                'carts',
                cleanName($res['dirname'] . DIRECTORY_SEPARATOR . $res['name'])
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
            $CartVariation->addMediaFromDisk(
                user: User::find(1),
                path: $fileInfo['path'],
                fileInfo: (object)$fileInfo
            );

            (new CleanFilesActions())->handle($request, [], [], $cart['cart']);
            return $fileInfo;

        });
//        $this->createReport('Diploma Results', $files->toArray(),$request);
        $request->merge([
            'ResultsAction' => [
                'cart_variation' => $cart['cart']->id,
                'status' => $cart['cart']->status,
                'attachments' => $files,
                'quantity' => $request['quantity'],
                'product' => $request['product']
            ]
        ]);
    }
}
