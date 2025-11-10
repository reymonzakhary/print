<?php

namespace App\Shop\Product;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Facades\Plugins;
use App\Http\Requests\Finder\FilterProductRequest;
use App\Http\Resources\Products\PrintProductPriceSemiCalcResource;
use App\Http\Resources\Products\PrintProductShopCalcResource;
use App\Services\Categories\Products\Prices\PriceService;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Calculations\CalculationService;
use App\Services\Tenant\Categories\SupplierCategoryService as CategoryService;
use App\Shop\Contracts\ShopProductInterface;
use GuzzleHttp\Exception\GuzzleException;
use Hyn\Tenancy\Environment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Illuminate\Http\Response;

class ShopPrintProduct implements ShopProductInterface
{
    /**
     * CalculateController constructor.
     *
     * @param PriceService $priceService
     * @param CalculationService $calculationService
     * @param SupplierCategoryService $supplierCategoryService
     * @param CategoryService $categoryService
     * @param Request $request
     * @param Environment $environment
     */
    public function __construct(
        private readonly PriceService            $priceService,
        private readonly CalculationService      $calculationService,
        private readonly SupplierCategoryService $supplierCategoryService,
        private readonly CategoryService         $categoryService,
        private readonly Request                 $request,
        private readonly Environment             $environment,
    )
    {
    }

    private string $category;

    /**
     * @param             $category
     * @return $this
     */
    public function setCategories(
        $category,
    ): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|JsonResponse|PrintProductShopCalcResource|PrintProductPriceSemiCalcResource
     *
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/categories/{category_id}/products?type=print",
     *     summary="Get All products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintProductShopResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function products(): PrintProductPriceSemiCalcResource|JsonResponse|Collection|PrintProductShopCalcResource
    {
        return match ($this->environment->tenant()->getAttribute('external')) {
            true => $this->handleExternalProduct(),
            default => $this->handleInternalProduct()
        };
    }

    /**
     * @return PrintProductShopCalcResource
     *
     * @throws GuzzleException
     */
    private function handleExternalProduct(): PrintProductShopCalcResource
    {
        [$quantity, $productData, $pluginManager] = [
            $this->request->integer('quantity'),
            $this->request->get('product'),
            Plugins::load(domain())
        ];

        if (!$categoryData = $this->categoryService->obtainCategory($this->category)['data'] ?? false) {
            throw new RuntimeException(
                sprintf(
                    'Category "%s" could not be found on tenant "%s"',
                    $this->category,
                    $this->environment->tenant()->getAttribute('uuid')
                )
            );
        }

        return PrintProductShopCalcResource::make(
            ItemDTO::fromExternalProduct(
                categoryData: $categoryData,
                quantity: $quantity,
                productData: $productData,
                priceData: $pluginManager->getPrice($quantity, $productData, $categoryData)
            ),
        );
    }

    /**
     * @throws GuzzleException
     * @throws ValidationException
     */
    private function handleInternalProduct(): PrintProductPriceSemiCalcResource|JsonResponse|Collection|PrintProductShopCalcResource
    {
        $cat = $this->supplierCategoryService->obtainCategoryObject($this->category);

        if (optional(optional($cat)['price_build'])['full_calculation']) {
            $proxy = app(CalculationService::class)
                ->obtainCalculatedShopPrices(
                    $this->category,
                    app(FilterProductRequest::class)->validated(),
                    request()->query()
                );

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ], optional($proxy)['status']);
            }

            return PrintProductShopCalcResource::make(
                ItemDTO::fromFullCalculation($proxy)
            );
        }

        if (optional(optional($cat)['price_build'])['semi_calculation']) {
            $proxy = app(CalculationService::class)
                ->obtainSemiCalculatedShopPrices($this->category, app(FilterProductRequest::class)->validated());

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ], optional($proxy)['status']);
            }

            return PrintProductPriceSemiCalcResource::make(
                ItemDTO::fromSemiCalculation($proxy)
            );
        }

        if (optional(optional($cat)['price_build'])['collection']) {
            $proxy = $this->priceService->obtainCollectionPrices($this->category, request()->toArray());

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ], optional($proxy)['status']);
            }

            return PrintProductPriceSemiCalcResource::collection(
                $proxy
            )->collection;
        }

        throw ValidationException::withMessages([
            'category' => [
                __("There is no valid calculation method selected.")
            ]
        ]);
    }

    /**
     * @return Collection|JsonResponse|PrintProductShopCalcResource|PrintProductPriceSemiCalcResource
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/categories/{category_id}/products?type=print",
     *     summary="List All products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *     description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintProductShopResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example=""),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function list()
    {
        $cat = $this->supplierCategoryService->obtainCategoryObject($this->category);

        if (optional(optional($cat)['price_build'])['full_calculation']) {

            $proxy = app(CalculationService::class)
                ->obtainCalculatedShopPriceList(
                    $this->category,
                    app(FilterProductRequest::class)->validated(),
                    request()->query()
                );

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ], optional($proxy)['status']);
            }

            if(!$proxy) {
                return response()->json([
                    'message' => __('The selected option not found!'),
                    'status' => 422
                ], 422);
            }

            return PrintProductShopCalcResource::make(
                ItemDTO::fromFullCalculation($proxy)
            );

        }

        if (optional(optional($cat)['price_build'])['semi_calculation']) {

            $proxy = app(CalculationService::class)
                ->obtainSemiCalculatedShopPriceList($this->category, app(FilterProductRequest::class)->validated());

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200
                || empty($proxy)
                ) {
                return response()->json([
                    'message' => optional($proxy)['message'] ??
                        __("We've found no prices with this combination..."),
                    'status' => optional($proxy)['status'] ??
                        Response::HTTP_UNPROCESSABLE_ENTITY
                ], optional($proxy)['status'] ??
                        Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return PrintProductPriceSemiCalcResource::make(
                ItemDTO::fromSemiCalculation($proxy)
            );

        }

        if (optional(optional($cat)['price_build'])['collection']) {
            $proxy = $this->priceService->obtainCollectionPrices($this->category, request()->toArray());

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return response()->json([
                    'message' => optional($proxy)['message'],
                    'status' => optional($proxy)['status']
                ], optional($proxy)['status']);
            }

            return PrintProductPriceSemiCalcResource::collection(
                $proxy
            )->collection;
        }

        throw ValidationException::withMessages([
            'category' => [
                __("There is no valid calculation method selected.")
            ]
        ]);

    }
}
