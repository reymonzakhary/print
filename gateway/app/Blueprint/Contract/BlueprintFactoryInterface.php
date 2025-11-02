<?php

namespace App\Blueprint\Contract;

use App\Models\Tenants\Product;

interface BlueprintFactoryInterface
{
    public function init(Product $product);
}
