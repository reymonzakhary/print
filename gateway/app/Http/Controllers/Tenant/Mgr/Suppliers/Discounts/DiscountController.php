<?php

namespace App\Http\Controllers\Tenant\Mgr\Suppliers\Discounts;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Tenant\Discounts\DiscountService;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DiscountController extends Controller
{
    use ConsumesExternalServices;

    /**
     * @var DiscountService
     */
    protected DiscountService $discountService;

    /**
     * DiscountController constructor.
     * @param DiscountService $discounts
     */
    public function __construct(
        DiscountService $discounts
    )
    {
        $this->discountService = $discounts;
    }


    // @todo get the category discount
    //


    /**
     * @param Supplier $supplier
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function index(
        Supplier $supplier
    )
    {

//        $this->discountService->()
        $proxy = $this->makeRequest('get', "/resellers/2aa21820-a0db-47da-ab97-828f60cd985b/suppliers/{$supplier->supplier_id}");

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Request  $request
     * @param Supplier $supplier
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function general(
        Request  $request,
        Supplier $supplier
    )
    {

        $proxy = $this->makeRequest('get',
            "/resellers/{$request->tenant->uuid}/suppliers/{$supplier->supplier_id}/general");

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Request  $request
     * @param Supplier $supplier
     * @param string   $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function category(
        Request  $request,
        Supplier $supplier,
        string   $category
    )
    {

        $proxy = $this->makeRequest('get',
            "/resellers/{$request->tenant->uuid}/suppliers/{$supplier->supplier_id}/categories/$category");

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Request  $request
     * @param Supplier $supplier
     * @param string   $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function categories(
        Request  $request,
        Supplier $supplier,
        string   $category
    )
    {

        $proxy = $this->makeRequest('get',
            "/resellers/{$request->tenant->uuid}/suppliers/{$supplier->supplier_id}/assortments");

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}
