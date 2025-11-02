<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Categories\Products;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Enums\Status;
use App\Facades\Plugins;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finder\FilterProductRequest;
use App\Http\Requests\Finder\FinderProductShopRequest;
use App\Http\Resources\Finder\FinderShopResource;
use App\Http\Resources\Products\PrintProductPriceSemiCalcResource;
use App\Http\Resources\Products\PrintProductResource;
use App\Http\Resources\Products\PrintProductShopCalcResource;
use App\Models\Hostname;
use App\Plugins\Moneys;
use App\Services\Margins\MarginService;
use App\Services\Tenant\Calculations\CalculationService;
use App\Services\Tenant\Categories\SupplierCategoryService;
use App\Services\Tenant\Finder\Categories\Products\ProductService;
use GuzzleHttp\Exception\GuzzleException;
use Hyn\Tenancy\Environment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    protected ProductService $productsService;
    protected MarginService $marginService;

    /**
     * ProductController constructor.
     * @param ProductService $productsService
     * @param MarginService $marginService
     */
    public function __construct(
        ProductService $productsService,
        MarginService $marginService
    )
    {
        $this->productsService = $productsService;
        $this->marginService = $marginService;

    }


    /**
     * @param FilterProductRequest $request
     * @param string               $category
     * @return string
     * @throws GuzzleException
     */
    public function filter(
        FilterProductRequest $request,
        string               $category
    )
    {
        $proxy = $this->productsService->obtainProductsByFilter($category, $request->query(), $request->validated());
        if (optional($proxy)['status'] !== 404) {
//            return FinderProductResource::collection($proxy)->additional([
            return PrintProductResource::collection($proxy)->additional([
                'page' => $proxy['page'],
                'per_page' => $proxy['per_page'],
                'total' => $proxy['total'],
                'lastPage' => $proxy['lastPage']
            ]);
        }
        return response()->json([
            'message' => __("We Can't handle this Request!"),
            'status' => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param FinderProductShopRequest $request
     * @param Environment $environment
     * @param string $category
     *
     * @return AnonymousResourceCollection
     * @throws \Exception
     */
    public function shop(
        FinderProductShopRequest $request,
        Environment          $environment,
        string               $category,
    )
    {
        $results = [];
        $currentSupplierUuid = $environment->website()->getAttribute('uuid');
        $currentTenant = [
            'uuid' => $currentSupplierUuid,
            'fqdn' => $environment->hostname()->getAttribute('fqdn')
        ];

        $req = $request->only(['type', 'quantity', 'divided']);

        foreach (collect($request->validated('suppliers'))->unique() as $supplier) {
            $contract = ContractManager::getContractWithSupplierByConnection(Hostname::class , $supplier);

            $itsNotMe = $supplier !== $currentSupplierUuid;
            if ($itsNotMe) {
                if($contract && !$contract->count() && $contract->st !== Status::ACCEPTED->value || !$contract || empty($contract->custom_fields)){
                    continue;
                }

                switchTenant($supplier);
            }
            $category_service = app(SupplierCategoryService::class)->obtainCategoriesByLink(
                $category,
                $supplier !== $currentSupplierUuid
            );

            if(optional($category_service)['status'] !== 200){
                continue;
            }

            collect(optional($category_service)['data'])->each(function ($category_model) use (
                $currentTenant,
                $request,
                $req,
                $contract,
                $supplier,
                &$results,
                $itsNotMe,
            ){
                $discount_data = null;
                $boop_data = collect(data_get($category_model, 'boops'))->first();
                $category_id = collect(data_get($category_model, '_id'))->first();
                if ($contract) {
                    $contract_fields = $contract->custom_fields;
                    $categories = optional(optional($contract_fields)->contract)['categories'] ?? [];
                    $discount_data = optional(optional($contract_fields)->contract)['discount'] ?? [];
                    if(!in_array($category_id, collect($categories)->flatten()->toArray())) {
                        return;
                    }
                }

                if (!$boop_data) {
                    return;
                }

                $available_manifest = collect($boop_data['boops'] ?? []);
                $product = [];
                $not_found = [];

                // Filter request data to match available boops
                collect($request->product)->each(function ($item) use (&$product, $available_manifest, &$not_found) {
                    if (empty($item['linked_key']) || empty($item['linked_value'])) {
                        return;
                    }

                    // Find the matching boop
                    $matchedBoop = $available_manifest->firstWhere('linked', $item['linked_key']);
                    if (!$matchedBoop) {
                        $not_found[] = $item;
                        return;
                    }

                    // Find the matching option inside `ops`
                    $matchedOp = collect($matchedBoop['ops'])->firstWhere('linked', $item['linked_value']);
                    if (!$matchedOp) {
                        $not_found[] = $item;
                        return;
                    }

                    // Add valid product data
                    $product[] = [
                        'key' => $matchedBoop['slug'],
                        'linked_key' => $matchedBoop['linked'],
                        'linked_value' => $matchedOp['linked'],
                        'key_id' => $matchedBoop['id'],
                        'value' => $matchedOp['slug'],
                        'value_id' => $matchedOp['id'],
                        'divider' => $item['divider'],
                        'dynamic' => (bool) optional($item)['dynamic'],
                        'source_key' => optional($matchedBoop)['source_slug'],
                        'source_value' => optional($matchedOp)['source_slug'],
                        '_' => $item['_']
                    ];
                });

                // Ensure only valid products are processed
                if (count($product) !== count($available_manifest)) {
                    return;
                }

                $supplier_request = new FilterProductRequest(
                    array_merge(
                        $req,
                        [
                            'product' => $product,
                            'contract' => $discount_data,
                        ]
                    )
                );

                $res = match (tenant()->external) {
                    true => $this->handleExternalProduct($category_model, $supplier_request, $currentTenant, $itsNotMe),
                    default => $this->handleInternalProduct($category_model, $supplier_request->all(), $currentTenant, $itsNotMe)
                };

                // Make the request
                $results[] = [
                    'supplier' => $supplier,
                    'results' => $res?->resource??[],
                ];
                $supplier_request = null;

            });

        }

        switchTenant($currentSupplierUuid);

        return FinderShopResource::collection(
            $results
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null,
            ]);
    }


    /**
     * @param $category
     * @param $request
     * @param $currentTenant
     * @param false $itsNotMe
     * @return PrintProductShopCalcResource
     * @throws GuzzleException
     */
    private function handleExternalProduct(
        $category,
        $request,
        $currentTenant,
        bool $itsNotMe = false
    ): PrintProductShopCalcResource
    {
        $quantity = $request->integer('quantity');
        $productData = $request->get('product');

        // Get base price and apply margin if needed
        $priceData = collect(
            Plugins::load(tenant()->hostname)->getPrice($quantity, $productData, $category)
        )->when($itsNotMe, fn($prices) =>
            $this->applyMyMargin($prices->all(), $quantity, $currentTenant['uuid'] ?? null)
        );

        return PrintProductShopCalcResource::make(
            ItemDTO::fromExternalProduct(
                categoryData: $category,
                quantity: $quantity,
                productData: $productData,
                priceData: $priceData,
                currentTenant: $currentTenant
            )
        );
    }

    /**
     * @param $category
     * @param $request
     * @param $currentTenant
     * @param bool $itsNotMe
     *
     * @return PrintProductPriceSemiCalcResource|JsonResponse|array|Collection|PrintProductShopCalcResource
     * @throws GuzzleException
     */
    private function handleInternalProduct(
        $category,
        $request,
        $currentTenant,
        bool $itsNotMe = false
    ): PrintProductPriceSemiCalcResource|JsonResponse|array|Collection|PrintProductShopCalcResource
    {

        if (optional(optional($category)['price_build'])['full_calculation']) {
            $proxy = app(CalculationService::class)
                ->obtainCalculatedShopPrices(
                    $category['slug'],
                    $request,
                    request()->query()
                );

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return [];
            }

            if($itsNotMe){
                $margin = Moneys::getMargin(optional($proxy)['quantity'], optional($currentTenant)['uuid']);
                if(count($margin)) {

                    $oldPrices = $proxy['prices'];
                    $proxy['prices'] = [];
                    
                    foreach($oldPrices as $price) {
                        array_push($proxy['prices'], Moneys::applyMyMargin($price, $margin));
                    }
                }
            }

            return PrintProductShopCalcResource::make(
                ItemDTO::fromFullCalculation($proxy)
            );
        }

        if (optional(optional($category)['price_build'])['semi_calculation']) {
            $proxy = app(CalculationService::class)
                ->obtainSemiCalculatedShopPrices(
                    $category['slug'],
                    $request
                );

            if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
                return [];
            }

            if($itsNotMe){
                $margin = Moneys::getMargin(optional($proxy)['quantity'], optional($currentTenant)['uuid']);
                if(count($margin)) {

                    $oldPrices = $proxy['prices'];
                    $proxy['prices'] = [];
                    
                    foreach($oldPrices as $price) {
                        array_push($proxy['prices'], Moneys::applyMyMargin($price, $margin));
                    }
                }
            }


            return PrintProductPriceSemiCalcResource::make(
                ItemDTO::fromSemiCalculation($proxy)
            );
        }

        return [];
    }

    /**
     * @param array $prices
     * @param int $quantity
     * @param string $currentTenant
     * @return array
     * @throws GuzzleException
     */
    protected function applyMyMargin(
        array $prices,
        int $quantity,
        string $currentTenant
    ): array
    {
        $margin = $this->getMargin($quantity, $currentTenant);

        if (empty($margin)) {
            return $prices;
        }

        // Pre-calculate margin type and value
        $isFixed = $margin['type'] === 'fixed';
        $marginValue = $margin['value'];
        $marginRatio = $isFixed ? 0 : $marginValue / 100;

        foreach ($prices as &$price) {
            $qty = $price['qty'];

            // Calculate margin profit
            $profit = $isFixed
                ? $marginValue
                : $price['selling_price_ex'] * $marginRatio;

            // Apply margin to selling price
            $price['selling_price_ex'] += $profit;

            // Update price and gross_price to match selling_price_ex
            $price['p'] = $price['selling_price_ex'];
            $price['gross_price'] = $price['selling_price_ex'];

            // Calculate selling price with VAT
            $vatRate = (float) $price['vat'];
            $price['selling_price_inc'] = $price['selling_price_ex'] * (1 + $vatRate / 100);

            // Calculate price per piece
            $price['gross_ppp'] = $qty > 0 ? $price['selling_price_ex'] / $qty : 0;
            $price['ppp'] = $qty > 0 ? $price['selling_price_inc'] / $qty : 0;

            // Set profit
            $price['profit'] = $profit;
            $price['margins'] = $margin;
        }
        unset($price);

        return $prices;
    }

    /**
     * @param int $quantity
     * @param string $currentTenant
     * @return array
     * @throws GuzzleException
     */
    protected function getMargin(
        int $quantity,
        string $currentTenant
    ): array
    {
        // Skip collect() overhead - direct array access
        $marginData = $this->marginService->obtainMargin($currentTenant)[0] ?? null;

        // Early validation
        if ((!$quantity && $quantity !== 0) || !$marginData || empty($marginData['slots'])) {
            return [];
        }

        $slots = $marginData['slots'];

        // Type check once instead of during iteration
        if (!is_array($slots)) {
            return [];
        }

        // Find matching slot with early return
        foreach ($slots as $slot) {
            $from = (int) ($slot['from'] ?? 0);
            $to = (int) ($slot['to'] ?? -1);

            // Simplified range check with early return
            if ($quantity >= $from && ($to === -1 || $quantity <= $to)) {
                return [
                    'value' => $slot['value'] ?? 0,
                    'type' => $slot['type'] ?? 0,
                ];
            }
        }

        return [];
    }


}
