<?php

namespace App\Blueprint\Contract;

use App\Models\Tenant\Product;

interface BlueprintFactoryInterface
{
    public function init(Product $product);
}
