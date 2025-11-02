<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Events\Tenant\Categories\PriceGenerateEvent;
use App\Http\Controllers\Controller;

class PriceGenerateController extends Controller
{
    public function generate(string $category)
    {
        event(new PriceGenerateEvent($category, request()->tenant->uuid));
        return response()->json([
            "message" => "Prices generation had been started, we will notify you later.",
            "status" => 200
        ]);
    }
}
