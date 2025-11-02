<?php

namespace App\Http\Controllers\Tenant\Mgr\Shops\Categories;

use App\Http\Controllers\Controller;
use App\Shop\Contracts\ShopCategoryInterface;

class CategoryController extends Controller
{

    /**
     * List all categories
     * @return mixed
     */
    public function index(): mixed
    {
        return app(ShopCategoryInterface::class)->categories();
    }

    /**
     * Get single category
     * @param string $category
     * @return mixed
     */
    public function show(
        string $category
    )
    {
        return app(ShopCategoryInterface::class)->category($category);
    }
}
