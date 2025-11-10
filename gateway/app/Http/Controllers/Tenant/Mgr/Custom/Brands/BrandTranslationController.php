<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Brands;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brands\BrandResource;
use App\Models\Tenant\Brand;
use Symfony\Component\HttpFoundation\Response;

class BrandTranslationController extends Controller
{
    /**
     * @param int $brand
     * @return mixed
     */
    public function __invoke(int $brand): mixed
    {
        $categories = Brand::query()
            ->where([['row_id', $brand], ['iso', '!=', app()->getLocale()]])
            ->get(['id', 'row_id', 'iso', 'name', 'description']);
        return BrandResource::collection(
            $categories
        )->hide([
            'slug', 'sort', 'published', 'created_by', 'published_by',
            'published_at', 'media',
            'created_at', 'updated_at'
        ])->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
