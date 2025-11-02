<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\CategoryResource;
use App\Utilities\Traits\ConsumesExternalServices;
use Illuminate\Http\Response;

class CategoryPresetController extends Controller
{
    use ConsumesExternalServices;

    public function index()
    {
        $proxy = $this->makeRequest('get',
            "/categories");

        return CategoryResource::collection(
            $proxy
        )
            ->hide([])
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }
}
