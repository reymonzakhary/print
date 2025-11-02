<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductCalculatePriceRequest;
use App\Services\Categories\Products\Prices\PriceCalculationService;
use Illuminate\Http\Response;

class CalculateController extends Controller
{
    /**
     * @var PriceCalculationService
     */
    protected PriceCalculationService $priceService;

    /**
     * Calculate Controller constructor.
     * @param PriceCalculationService $priceService
     */
    public function __construct(
        PriceCalculationService $priceService
    )
    {
        $this->priceService = $priceService;
    }

    /**
     * @OA\Post(
     *   tags={"Products Calculate price"},
     *   path="/api/v1/mgr/categories/{category}/products/calculate/prices",
     *   summary="Calculate Products price",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(
     *          @OA\Property(type="array", title="products", description="products", property="products", @OA\Items(
     *              @OA\Property(type="string", title="finishing", description="finishing", property="finishing", example="without-luxury-finishing"),
     *              @OA\Property(type="string", title="format", description="format", property="format", example="140-x-70-cm"),
     *              @OA\Property(type="string", title="weight", description="weight", property="weight", example="80-grs")
     *          )),
     *          @OA\Property(type="string", title="quantity", description="quantity", property="quantity", example="100"),
     *      ),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", title="data", description="data", property="data", @OA\Items(
     *              @OA\Property(type="array", title="digital", description="digital", property="digital", @OA\Items(
     *                  @OA\Property(type="string", title="dlv", description="dlv", property="dlv", example="2"),
     *                  @OA\Property(type="array", title="prices", description="prices", property="prices", @OA\Items(
     *                      @OA\Property(type="string", title="start_cost", description="start_cost", property="start_cost", example="2060"),
     *                      @OA\Property(type="string", title="subtotal", description="subtotal", property="subtotal", example="12500"),
     *                      @OA\Property(type="string", title="total", description="total", property="total", example="14560"),
     *                      @OA\Property(type="string", title="qty", description="qty", property="qty", example="100"),
     *                  )),
     *              )),
     *          )),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Box has been created successfully"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Unprocessable Entity",
     *     @OA\JsonContent(
     *          @OA\Property(format="string", title="message", example="Category Not Exist", description="message", property="message"),
     *          @OA\Property(format="string", title="status", example="404", description="status", property="status"),
     *     ),
     *   )
     * )
     */
    public function index(
        ProductCalculatePriceRequest $request,
        string                       $category
    )
    {

        $proxy = $this->priceService->obtainCalculatedPrices($category, $request->all());

        return response()->json([
            'data' => $proxy,
            'message' => Null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}
