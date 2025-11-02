<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\CategoryResource;
use App\Models\Tenants\Category;
use Symfony\Component\HttpFoundation\Response;

class CategoryTranslationController extends Controller
{
    /**
     * @param int $category
     * @return mixed
     */
    public function __invoke(int $category): mixed
    {
        $categories = Category::query()
            ->where([['row_id', $category], ['iso', '!=', app()->getLocale()]])
            ->get(['id', 'row_id', 'iso', 'name', 'description']);
        return CategoryResource::collection(
            $categories
        )->hide([
            'parent_id',
            'id',
            'slug',
            'sort',
            'base_id',
            'has_children',
            'is_parent',
            'media', 'margin_value', 'margin_type',
            'discount_value', 'discount_type', 'published',
            'published_at', 'published_by', 'created_by',
            'created_at', 'updated_at', 'children'
        ])->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
