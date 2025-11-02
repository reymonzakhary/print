<?php

namespace App\Blueprint\Processors;

use App\Http\Requests\Cart\CartStoreRequest;

class FindAndReplaceProcessor
{
    public function handle(CartStoreRequest $request)
    {
        return ['data'];
    }
}
