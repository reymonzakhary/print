<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Products\PrintProductResource;
use App\Http\Resources\Products\ProductResource;
use App\Services\Suppliers\SupplierProductService;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    use ConsumesExternalServices;

    public function __construct(public SupplierProductService $supplierProductService)
    {
        # code...
    }

    /**
     * @param Request $request
     * @param string  $category
     * @return JsonResponse|AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(
        Request $request,
        string  $category
    )
    {
        return PrintProductResource::collection($this->supplierProductService->obtainProducts($category, $request->all()));

    }

    /**
     * @param Request $request
     * @param string  $category
     * @param string  $product
     * @return ProductResource|JsonResponse
     * @throws GuzzleException
     */
    public function show(
        Request $request,
        string  $category,
        string  $product
    )
    {
        $proxy = $this->makeRequest('get',
            "/resellers/{$request->tenant->uuid}/assortments/{$category}/products/{$product}");

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function filter(
        Request $request,
        string  $category
    )
    {
        return PrintProductResource::collection($this->supplierProductService->obtainProductsFillter($category, $request->all()));
    }

}
