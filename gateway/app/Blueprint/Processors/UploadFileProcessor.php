<?php

namespace App\Blueprint\Processors;

use App\Http\Requests\Cart\CartStoreRequest;
use App\Models\Tenant\Cart;

class UploadFileProcessor
{
    public function handle($node, CartStoreRequest $request)
    {
        $cart = Cart::whereUuid(session(tenant()->uuid . '_cart_session'))->first();
        foreach (collect($node->config())->except('Approval')->toArray() as $key => $value) {
            $cart->addMediaCollection("cart_{$request->sku->id}")->useDisk('carts');
            $cart->addMediaFromRequest($key);
        }
    }


}
