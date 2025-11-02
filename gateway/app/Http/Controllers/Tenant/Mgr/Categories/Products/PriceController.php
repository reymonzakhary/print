<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Products;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\Products\PrintProductPriceSemiCalcResource;
use App\Http\Resources\Products\PrintProductShopCalcResource;
use App\Services\Categories\Products\Prices\PriceService;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Calculations\CalculationService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    /**
     * @var PriceService
     */
    protected PriceService $priceService;

    /**
     * @var CalculationService
     */
    protected CalculationService $calculationService;

    /**
     * @var SupplierCategoryService
     */
    protected SupplierCategoryService $supplierCategoryService;

    /**
     * CalculateController constructor.
     * @param PriceService            $priceService
     * @param CalculationService        $calculationService
     * @param SupplierCategoryService $supplierCategoryService
     */
    public function __construct(
        PriceService            $priceService,
        CalculationService        $calculationService,
        SupplierCategoryService $supplierCategoryService
    )
    {
        $this->priceService = $priceService;
        $this->calculationService = $calculationService;
        $this->supplierCategoryService = $supplierCategoryService;
    }

    /**
     * @param string  $category
     * @param Request $request
     * @return PrintProductShopCalcResource|JsonResponse|Collection|PrintProductPriceSemiCalcResource
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function index(
        string  $category,
        Request $request
    )
    {
        $cat = $this->supplierCategoryService->obtainCategoryObject($category);


        if(optional(optional($cat)['price_build'])['full_calculation']) {
            $proxy = $this->calculationService->obtainCalculatedPrices($category, $request->toArray());
            if(optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ],  optional($proxy)['status']);
            }
            return PrintProductShopCalcResource::make(
                ItemDTO::fromFullCalculation($proxy)
            );
        } else if(optional(optional($cat)['price_build'])['semi_calculation']) {
            $proxy = $this->calculationService->obtainSemiCalculatedPrices($category, $request->toArray());
        } else if (optional(optional($cat)['price_build'])['collection']) {
            $proxy = $this->priceService->obtainCalculatePrices($category, $request->toArray());
        }else{
            throw ValidationException::withMessages([
                'category' => [
                    __("There is no valid calculation method selected.")
                ]
            ]);
        }

        if(optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
            return response()->json([
                'message' => optional($proxy)['message'],
                'status' => optional($proxy)['status']
            ],  optional($proxy)['status']);
        }
        return PrintProductPriceSemiCalcResource::make(
            ItemDTO::fromSemiCalculation($proxy)
        );

    }

    /**
     * @param Request $request
     * @param string  $category
     * @param string  $combination
     * @return string
     * @throws GuzzleException
     */
    public function store(
        Request $request,
        string  $category,
        string  $combination
    )
    {
        return $this->priceService->obtainStorePrices(
            $category,
            $combination,
            $request->all()
        );
    }
}
