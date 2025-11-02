<?php

namespace App\Http\Controllers\System\Mgr\Categories\Products;

use App\Http\Controllers\Controller;
use App\Services\System\Categories\Products\ProductService;
use GuzzleHttp\Exception\GuzzleException;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    protected ProductService $productService;

    /**
     * Create a new controller instance.
     *
     * @param ProductService $productService
     */
    public function __construct(
        ProductService $productService
    )
    {
        $this->middleware('auth');
        $this->productService = $productService;

    }

    /**
     * @param string $category_id
     * @return string
     * @throws GuzzleException
     */
    public function index(
        string $category_id
    )
    {
        return $this->productService->obtainProductsByCategoryId($category_id);
    }
}
