<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Contract\BluePrintActionContract;

class AddMultipleSignatureAction implements BluePrintActionContract
{
//    use HasReportingTraits;
    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $pdfs = data_get($request->toArray(), $data['input']['from']);

        app(GetPositionsAction::class)->handle($request, $data, $node, $cart);
        $newData = $data;

        $res = $request->GetPositionsAction->map(function ($pdf, $key) use ($node, $data, $request, $cart, $newData, $pdfs) {
            $newData['input']['from'] = 'TempGetPositionsAction';
            $request->merge(
                [
                    'TempGetPositionsAction' => collect([$key => collect($pdf)->merge($pdfs[$key])])
                ]
            );
            app(AddSignatureAction::class)->handle($request, $newData, $node, $cart);
            return $request->AddSignatureAction;
        });

//        $this->createReport('Add Multiple Signature Action', [
//            $data['as'] => $res
//        ], $request);

        $request->merge([
            'AddMultipleSignatureAction' => $res
        ]);
    }
}
