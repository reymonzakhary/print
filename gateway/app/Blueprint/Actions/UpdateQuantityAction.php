<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Models\Tenants\CartVariation;

class UpdateQuantityAction implements BluePrintActionContract
{

    use HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $from = data_get($request->toArray(), $data['input']['from']);
        $cartVariation = CartVariation::find($cart['cart']->id);

        match ((bool)optional($request->cart)['updated']) {
            true => match (optional($data['input'])['incremental']) {
                true => call_user_func(function () use ($cartVariation, $request) {
                    $request->merge([
                        'cart' => [
                            'updated' => true
                        ]
                    ]);
                    $cartVariation->update([
                        'qty' => $cartVariation->qty + $request->numOfRows
                    ]);
                }),
                false => false
            },
            false => match (optional($data['input'])['incremental']) {
                true, false => call_user_func(function () use ($cartVariation, $request) {
                    $request->merge([
                        'cart' => [
                            'updated' => true
                        ]
                    ]);
                    $cartVariation->update([
                        'qty' => $request->numOfRows
                    ]);
                }),
            },
            default => false
        };
//        $this->createReport('Update Quantity Action', $from, $request);
        $request->merge([
            'UpdateQuantityAction' => $from,
        ]);
    }
}
