<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\SystemManifestResource;
use App\Services\System\Categories\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryManifestController extends Controller
{
    /**
     * Handle the request to obtain the system category manifest based on the provided category link.
     *
     * @param string $linked The category link
     * @return SystemManifestResource|array|JsonResponse
     */
    public function __invoke(
        string $linked
    )
    {
        if($linked === 'undefined') {
            return response()->json([
                'message' => __("Undefined is not a category!"),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        $category = app(CategoryService::class)->obtainSystemCategoryManifest($linked);

        return $category?
            SystemManifestResource::make($category):
            [
                "data" => []
            ];
    }
}
