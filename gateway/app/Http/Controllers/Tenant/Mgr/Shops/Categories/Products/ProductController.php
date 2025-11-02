<?php

namespace App\Http\Controllers\Tenant\Mgr\Shops\Categories\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\ProductsRequest;
use App\Shop\Contracts\ShopProductInterface;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    /**
     * Fetches and returns products based on the requested category from the specified shop.
     *
     * @param ProductsRequest $request The request object for products.
     * @param ShopProductInterface $shop The shop from which products are fetched.
     * @param mixed $category The category of products to fetch.
     * @return mixed The mixed of products under the specified category.
     */
    public function index(
        ProductsRequest $request,
        ShopProductInterface $shop,
        mixed $category
    ): mixed
    {
        return $shop->setCategories($category)->products();
    }

    /**
     * Fetches and returns the list of products based on the requested category from the specified shop.
     *
     * @param ProductsRequest $request The request object for products.
     * @param ShopProductInterface $shop The shop from which products list is fetched.
     * @param mixed $category The category of products for which the list is generated.
     * @return mixed The list of products under the specified category from the shop.
     */
    public function list(
        ProductsRequest $request,
        ShopProductInterface $shop,
        mixed $category
    ): mixed
    {
        return $shop->setCategories($category)->list();
    }
}
