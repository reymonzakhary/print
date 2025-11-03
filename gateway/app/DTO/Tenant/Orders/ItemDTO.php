<?php

namespace App\DTO\Tenant\Orders;

use App\Enums\Status;
use App\Facades\Settings;
use App\Models\Domain;
use App\Models\Tenants\Box;
use App\Models\Tenants\Option;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Calculations\CalculationService;
use App\Services\Tenant\Categories\SupplierCategoryService as CategoryService;
use Carbon\Carbon;
use Cmixin\BusinessDay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @author Reymon Zakhary
 *
 * @method static array|null  fromRequest(array $request)
 * @method static array|null  fromExternal(array $request)
 * @method static array|null  fromOpenProduct(array $request)
 * @method static array|null  fromProductToItems(array $request)
 * @method static array  fromOpenProductPrice(array $category, array $old_price, array $price)
 * @method static array  fromFullCalculation(array $request)
 * @method static array  fromExternalProduct(array $categoryData, int $quantity, array $productData, array $priceData, array $currentTenant = [])
 * @method static array  fromUpdateFullCalculation(array $request)
 * @method static array  fromSemiCalculation(array $request)
 * @method static array  fromUpdateSemiCalculation(array $request)
 * @method static Builder|Model|object|null  fromPrintDB(Collection $collection)
 * @method static array  fromGrossPrice(string $category_slug, array $price, mixed $gross_price, ?int $qty, ?int $dlv, ?float $vat)
 * @method static array  fromShopCustom(mixed $item, string $signature, \stdClass|Request $request, int $status)
 *
 * @see \App\DTO\Tenant\Orders\ItemDTO;
 */
class ItemDTO
{
    private array $category;

    /**
     * @param array $request
     * @return array
     * @throws ValidationException
     */
    public function request(
        array $request
    ): array
    {
        $this->category = $this->getCategory(optional(optional($request)['category'])['slug']);
        $price = optional(optional($request)['price'])['gross_price'];
        $vat = $this->category['vat'] ?? 0;
        $qty = optional($request)['quantity'];
        return [
            'custom' => false,
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'variation' => [],
            'type' => 'print',
            'calculation_type' => $request['calculation_type'],
            'items' => $this->getItems(
                optional(optional($request)['category'])['slug'],
                optional($request)['product']
            ),
            'product' => optional($request)['product'],
            'connection' => tenant()->uuid,
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
            'external' => false,
            'external_id' => tenant()->uuid,
            'external_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
            'variations' => [],
            'category' => $this->category,
            'margins' => [],
            'divided' => false,
            'quantity' => $qty,
            'calculation' => [],
            'hasVariation' => false,
            'price' => [
                "id" => null,
                "vat_p" => $price * $vat / 100,
                "vat_ppp" => ($price * $vat / 100) / $qty,
                "pm" => null,
                "qty" => $qty,
                "dlv" => $this->getBusinessDay($this->category, optional(optional($request)['price'])['dlv'] ?? []),
                "gross_price" => $price,
                "gross_ppp" => $price / $qty,
                "p" => $price,
                "ppp" => $price / $qty,
                "selling_price_ex" => $price,
                "selling_price_inc" => ($vat + 100) * $price / 100,
                "profit" => 0,
                "discount" => [],
                "margins" => [],
                "vat" => $vat
            ]
        ];
    }


    /**
     * @param array $request
     * @return array
     * @throws ValidationException
     */
    public function external(
        array $request
    ): array
    {
        $this->category = $this->getOrLayoutCategory(
            optional($request)['category_name'],
            optional($request)['category_slug'],
            optional($request)['supplier_id'],
            optional($request)['supplier_name'],
        );

        $price = optional(optional($request)['price'])['p'];
        $vat = $this->category['vat'] ?? 0;
        $qty = optional(optional($request)['price'])['qty'];
        $supplier = Domain::with('website')->where('fqdn', optional($request)['supplier_name'])->first();
        return [
            "vat" => $vat,
            "reference" => optional($request)['reference'],
            "delivery_separated" => optional($request)['delivery_separated'],
            "st" => Status::NEW->value,
            "supplier_id" => $supplier->website->uuid,
            "supplier_name" => $supplier?->custom_fields?->pick('company_name') ?? $supplier->fqdn,
            "connection" => optional($request)['connection'] ?? $supplier->website->uuid,
            "internal" => optional($request)['internal'],
            "note" => optional($request)['note'],
            "product" => [
                'custom' => false,
                'signature' => null,
                'product_id' => null,
                'product_name' => null,
                'product_slug' => null,
                'variation' => [],
                'type' => 'print',

                'calculation_type' => $request['calculation_type'],
                'items' => $this->handelItemsLayoutFromProduct(
                    optional($request)['product']
                ),
                'product' => $this->handelExternalProduct(optional($request)['product']),
                'connection' => $supplier->website->uuid,
                'tenant_id' => $supplier->website->uuid,
                'tenant_name' => $supplier?->custom_fields?->pick('company_name') ?? $supplier->fqdn,
                'external' => false,
                'external_id' => $supplier->website->uuid,
                'external_name' => $supplier?->custom_fields?->pick('company_name') ?? $supplier->fqdn,
                'variations' => [],
                'category' => $this->category,
                'margins' => [],
                'divided' => false,
                'quantity' => $qty,
                'calculation' => [],
                'hasVariation' => false,
                'price' => [
                    "id" => null,
                    "vat_p" => $price * $vat / 100,
                    "vat_ppp" => ($price * $vat / 100) / $qty,
                    "pm" => null,
                    "qty" => $qty,
                    "dlv" => $this->getBusinessDay($this->category, optional(optional($request)['price'])['dlv'] ?? []),
                    "gross_price" => $price,
                    "gross_ppp" => $price / $qty,
                    "p" => $price,
                    "ppp" => $price / $qty,
                    "selling_price_ex" => $price,
                    "selling_price_inc" => ($vat + 100) * $price / 100,
                    "profit" => 0,
                    "discount" => [],
                    "margins" => [],
                    "vat" => $vat
                ]
            ]

        ];
    }


    /**
     * @param mixed $item
     * @param string $signature
     * @param Request|\stdClass $request
     * @param int $status
     * @return array
     */
    public function shopCustom(
        mixed             $item,
        string            $signature,
        Request|\stdClass $request,
        int               $status
    ): array
    {
        $qty = $item->qty > 0 ? $item->qty : 1;
        $product = Product::where('row_id', (int)$item->product_id)->first();
        $price = $item->price->multiply(100)->multiply($qty)->amount();
        $vat = Settings::vat()?->value;
        $sku = Sku::find((int)$item->sku_id)->with('product')->first();

        return [
            'sku_id' => $item->sku_id,
            'product' => [
                'custom' => true,
                'type' => 'custom',
                'calculation_type' => '',
                'signature' => $signature,
                'product_id' => $product->row_id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'items' => $this->getItemsFromCustom(
                    $product,
                    $item
                ),
                'product' => [$product->toArray()],
                'connection' => tenant()->uuid,
                'tenant_id' => tenant()->uuid,
                'tenant_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
                'external' => false,
                'external_id' => tenant()->uuid,
                'external_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
                'variation' => $item?->variation,
                'category' => $product->category->toArray(),
                'margins' => [],
                'divided' => false,
                'quantity' => $qty,
                'calculation' => [],
                'hasVariation' => $item?->variation && count($item->variation),
                'price' => [
                    "id" => $sku->id,
                    "vat_p" => $price * $vat / 100,
                    "vat_ppp" => ($price * $vat / 100) / $qty,
                    "pm" => null,
                    "qty" => $qty,
                    "dlv" => [],
                    "gross_price" => $price,
                    "gross_ppp" => $price / $qty,
                    "p" => $price,
                    "ppp" => $price / $qty,
                    "selling_price_ex" => $price,
                    "selling_price_inc" => ($vat + 100) * $price / 100,
                    "profit" => 0,
                    "discount" => [],
                    "margins" => [],
                    "vat" => $vat
                ]
            ],
            'reference' => $item->reference,
            'delivery_separated' => $request->delivery_separated,
            'vat_id' => $request->vat_id,
            'st' => $status,
            'supplier_id' => $request->tenant->uuid,


        ];
    }

    public function openProductPrice(
        array $category,
        array $old_price,
        array $request
    ): array
    {
        $vat = is_numeric(optional($request)['vat']) ? optional($request)['vat'] : $old_price['vat'];
        $qty = optional($request)['qty'] ?: $old_price['qty'];
        $price = optional($request)['gross_price'] ?: $old_price['gross_price'];

        return [
            "id" => null,
            "vat_p" => $price * $vat / 100,
            "vat_ppp" => ($price * $vat / 100) / $qty,
            "pm" => null,
            "qty" => $qty,
            "dlv" => $this->getBusinessDay($category, optional($request)['dlv'] ?? $old_price['dlv']),
            "gross_price" => $price,
            "gross_ppp" => $price / $qty,
            "p" => $price,
            "ppp" => $price / $qty,
            "selling_price_ex" => $price,
            "selling_price_inc" => ($vat + 100) * $price / 100,
            "profit" => 0,
            "discount" => [],
            "margins" => [],
            "vat" => $vat
        ];
    }

    /**
     * Opens a product with the given request data.
     *
     * @param array $request The request data containing product details.
     * @return array The product information after processing.
     * @throws ValidationException
     */
    public function openProduct(
        array $request
    ): array
    {
        $this->category = $this->buildCategory(optional(optional($request)['category'])['name']);

        $price = optional(optional($request)['price'])['gross_price'];
        $vat = isset(optional($request)['price']['vat']) ? optional(optional($request)['price'])['vat'] : optional($this->category)['vat'];
        $qty = optional($request)['quantity'];

        return [
            'custom' => false,
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'variation' => [],
            'type' => 'print',
            'calculation_type' => $request['calculation_type'],
            'items' => $this->getItems(
                optional(optional($request)['category'])['name'],
                optional($request)['product'],
                'open_product'
            ),
            'product' => optional($request)['product'],
            'connection' => tenant()->uuid,
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
            'external' => false,
            'external_id' => tenant()->uuid,
            'external_name' => domain()?->custom_fields?->pick('company_name') ?? domain()->fqdn,
            'variations' => [],
            'category' => $this->category,
            'margins' => [],
            'divided' => false,
            'quantity' => $qty,
            'calculation' => [],
            'hasVariation' => false,
            'price' => [
                "id" => null,
                "vat_p" => $price * $vat / 100,
                "vat_ppp" => ($price * $vat / 100) / $qty,
                "pm" => null,
                "qty" => $qty,
                "dlv" => $this->getBusinessDay($this->category, optional(optional($request)['price'])['dlv'] ?? []),
                "gross_price" => $price,
                "gross_ppp" => $price / $qty,
                "p" => $price,
                "ppp" => $price / $qty,
                "selling_price_ex" => $price,
                "selling_price_inc" => ($vat + 100) * $price / 100,
                "profit" => 0,
                "discount" => [],
                "margins" => [],
                "vat" => $vat
            ]
        ];
    }

    /**
     * Handles processing the given collection of items.
     * This method retrieves relevant data from each item in the collection and constructs a new structured array based on the extracted data.
     *
     * @param SupportCollection|Collection $collection The collection of items to process.
     * @return array The processed array containing structured data for each item in the collection.
     *
     */
    protected function printDB(
        SupportCollection|Collection $collection
    ): array
    {
        $results = [];
        $collection->each(/**
         * @throws ValidationException
         */ function ($item) use (&$results) {

            if (optional($item->product)['calculation_type'] || optional($item->product)?->custom) {
                return;
            }

            $this->category = optional($item)['category'] ?? [
                'id' => optional($item->product)['category_id'],
                'name' => optional($item->product)['category_name'],
                'slug' => optional($item->product)['category_slug'],
                'display_name' => setDisplayName(optional($item->product)['category_name']),
                'vat' => Settings::vat()?->value,
            ];

            $qty = (int)optional(optional(optional($item->product)['prices'])['tables'])['qty'] ?? 0;
            $p = (int)optional(optional(optional($item->product)['prices'])['tables'])['p'] ?? 0;
            $vat = (int)optional(optional(optional($item->product)['prices'])['tables'])['vat'] ?? 0;
            $results[$item->id] = [
                'custom' => false,
                'signature' => null,
                'product_id' => null,
                'product_name' => null,
                'product_slug' => null,
                'variation' => [],
                'type' => 'print',
                'calculation_type' => 'full_calculation',
                'items' => $this->handelItemsLayout(optional($item->product)['object']),
                'product' => $this->handelProductLayout(optional($item->product)['object']),
                'connection' => optional(optional($item->product)['prices'])['supplier_id'],
                'tenant_id' => optional(optional($item->product)['prices'])['supplier_id'],
                'tenant_name' => optional(optional($item->product)['prices'])['supplier_name'],
                'external' => false,
                'external_id' => optional(optional($item->product)['prices'])['supplier_id'],
                'external_name' => optional(optional($item->product)['prices'])['supplier_name'],
                'variations' => [],
                'category' => $this->category,
                'margins' => [],
                'divided' => false,
                'quantity' => optional(optional(optional($item->product)['prices'])['tables'])['qty'],
                'calculation' => [],
                'hasVariation' => false,
                'price' => [
                    "id" => optional($item)['id'],
                    "vat_p" => $qty === 0 ? 0 : $p * $vat / 100,
                    "vat_ppp" => $qty === 0 ? 0 : ($p * $vat / 100) / $qty,
                    "pm" => optional(optional(optional($item->product)['prices'])['tables'])['pm'],
                    "qty" => $qty,
                    "dlv" => $this->getBusinessDay($this->category, optional(optional(optional($item->product)['prices'])['tables'])['dlv'] ?? []),
                    "gross_price" => $p,
                    "gross_ppp" => $qty === 0 ? $p : $p / $qty,
                    "p" => $p,
                    "ppp" => $qty === 0 ? $p : $p / $qty,
                    "selling_price_ex" => $p,
                    "selling_price_inc" => $p === 0 ? 0 : ($vat + 100) * $p / 100,
                    "profit" => 0,
                    "discount" => optional(optional(optional($item->product)['prices'])['tables'])['discount'] ?? [],
                    "margins" => optional(optional(optional($item->product)['prices'])['tables'])['margins'] ?? [],
                    "vat" => $vat
                ]
            ];
        });

        return $results;
    }


    /**
     * Performs a full calculation.
     *
     * @param array $request The calculation request.
     * @return array The processed calculation results.
     * @throws ValidationException
     */
    protected function updateFullCalculation(
        array $request
    ): array
    {
        $this->category = $this->handelCategoryLayout(optional($request)['category']);
        return [
            'custom' => false,
            'type' => optional($request)['type'],
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'hasVariation' => false,
            'variation' => [],
            'calculation_type' => optional($request)['calculation_type'],
            'items' => $this->handelItemsLayout(optional($request)['items'], optional($request)['product']),
            'product' => $this->handelProductLayout(optional($request)['product']),
            'connection' => optional($request)['connection'],
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()->fqdn,
            'external' => optional($request)['external_id'] !== tenant()->uuid,
            'external_id' => optional($request)['external_id'],
            'external_name' => optional($request)['external_name'],
            'category' => $this->category,
            'margins' => optional($request)['margins'],
            'divided' => optional($request)['divided'],
            'quantity' => optional($request)['quantity'],
            'calculation' => $this->handelCalculationLayout(optional($request)['calculation']),
            'price' => collect($this->handelPricesLayout(optional($request)['prices'] ?? []))->first()
        ];
    }

    /**
     * Performs a full calculation.
     *
     * @param array $request The calculation request.
     * @return array The processed calculation results.
     * @throws ValidationException
     */
    protected function fullCalculation(
        array $request
    ): array
    {
        $this->category = $this->handelCategoryLayout(optional($request)['category']);
        return [
            'custom' => false,
            'type' => optional($request)['type'],
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'hasVariation' => false,
            'variation' => [],
            'calculation_type' => optional($request)['calculation_type'],
            'items' => $this->handelItemsLayout(optional($request)['items'],optional($request)['product']),
            'product' => $this->handelProductLayout(optional($request)['product']),
            'connection' => optional($request)['connection'],
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()->fqdn,
            'external' => optional($request)['external_name'] !== domain()->fqdn,
            'external_id' => optional($request)['external_id'],
            'external_name' => optional($request)['external_name'],
            'category' => $this->category,
            'margins' => optional($request)['margins'],
            'divided' => optional($request)['divided'],
            'quantity' => optional($request)['quantity'],
            'calculation' => $this->handelCalculationLayout(optional($request)['calculation']),
            'prices' => $this->handelPricesLayout(optional($request)['prices'] ?? [])
        ];
    }

    /**
     * @param array $categoryData
     * @param int $quantity
     * @param array $productData
     * @param array $priceData
     * @param array $currentTenant
     * @return array
     *
     */
    protected function externalProduct(
        array $categoryData,
        int   $quantity,
        array $productData,
        array $priceData,
        array $currentTenant = []
    ): array
    {
        return [
            'type' => 'print',
            'calculation_type' => 'external_calculation',

            'connection' => tenant()->getAttribute('uuid'),

            'tenant_id' => $currentTenant['uuid'] ?? tenant()->getAttribute('uuid'),
            'tenant_name' => $currentTenant['fqdn'] ?? tenant()->domains()->first()?->getAttribute('fqdn'),

            "external" => true,
            "external_id" => tenant()->getAttribute('uuid'),
            "external_name" => tenant()->domains()->first()?->getAttribute('fqdn'),

            'items' => $this->getItemsForExternalProduct($categoryData, $productData),

            'category' => $categoryData,
            'quantity' => $quantity,
            'product' => $productData,
            'prices' => $priceData,
            'calculation' => [],
            'margins' => [],
            'discounts' => [],
            'divided' => $categoryData['divided'] ?? false,
        ];
    }

    /**
     * @param array $request
     * @return array
     * @throws ValidationException
     */
    protected function updateSemiCalculation(
        array $request
    ): array
    {
        if (optional($request)['status'] == 422) {
            throw ValidationException::withMessages([
                "error" => $request['message'],
            ]);
        }
        $this->category = $this->handelCategoryLayout(optional($request)['category']);
        return [
            'custom' => false,
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'hasVariation' => false,
            'variation' => [],
            'type' => optional($request)['type'],
            'calculation_type' => optional($request)['calculation_type'],
            'items' => $this->handelItemsLayout(optional($request)['items'],optional($request)['product']),
            'product' => $this->handelProductLayout(optional($request)['product']),
            'connection' => optional($request)['connection'],
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()->fqdn,
            'external' => optional($request)['external_id'] !== tenant()->uuid,
            'external_id' => optional($request)['external_id'],
            'external_name' => optional($request)['external_name'],
            'category' => $this->category,
            'margins' => optional($request)['margins'],
            'divided' => optional($request)['divided'],
            'quantity' => optional($request)['quantity'],
            'calculation' => $this->handelCalculationLayout(optional($request)['calculation']),
            'price' => collect($this->handelPricesLayout(optional($request)['prices'] ?? []))->first()
        ];
    }


    protected function semiCalculation(
        array $request
    ): array
    {
        $this->category = $this->handelCategoryLayout(optional($request)['category']);
        return [
            'custom' => false,
            'signature' => null,
            'product_id' => null,
            'product_name' => null,
            'product_slug' => null,
            'hasVariation' => false,
            'variation' => [],
            'type' => optional($request)['type'],
            'calculation_type' => optional($request)['calculation_type'],
            'items' => $this->handelItemsLayout(optional($request)['items'],optional($request)['product']),
            'product' => $this->handelProductLayout(optional($request)['product']),
            'connection' => optional($request)['connection'],
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()->fqdn,
            'external' => optional($request)['external_id'] !== tenant()->uuid,
            'external_id' => optional($request)['external_id'],
            'external_name' => optional($request)['external_name'],
            'category' => $this->category,
            'margins' => optional($request)['margins'],
            'divided' => optional($request)['divided'],
            'quantity' => optional($request)['quantity'],
            'calculation' => $this->handelCalculationLayout(optional($request)['calculation']),
            'prices' => $this->handelPricesLayout(optional($request)['prices'])
        ];
    }

    /**
     * Calculate gross price based on category, price, quantity, delivery, and VAT.
     *
     * @param string $category_slug The category slug.
     * @param array $price The price details.
     * @param mixed $gross_price The gross price.
     * @param int|null $gty The quantity, if provided.
     * @param int|null $dlv The delivery value, if provided.
     * @param float|null $vat The VAT percentage, if provided.
     * @return array The processed data containing various price details.
     *
     */
    public function grossPrice(
        string $category_slug,
        array  $price,
        mixed  $gross_price,
        ?int   $gty = null,
        ?int   $dlv = null,
        ?float $vat = null,
    ): array
    {
        $cat = app(SupplierCategoryService::class)->obtainCategoryObject($category_slug);
        $percentage = $vat ?? optional($price)['vat'];
        $qty = $gty ?? optional($price)['qty'];
        $ppp = $gross_price / $qty;
        $vat_p = ($percentage * $gross_price / 100) ?? optional($price)['vat_p'];
        return [
            "id" => null,
            "p" => $gross_price,
            "pm" => optional($price)['pm'],
            "dlv" => $dlv ? $this->getBusinessDay($cat, [
                "day" => null,
                "days" => $dlv,
                "year" => null,
                "month" => null,
                "day_name" => null,
                "actual_days" => null
            ]) : optional($price)['dlv'],
            "ppp" => $ppp,
            "qty" => $qty,
            "vat" => $percentage,
            "vat_p" => $vat_p,
            "vat_ppp" => $vat_p / $qty,
            "profit" => optional($price)['profit'] ?? 0,

            "gross_price" => $gross_price,
            "gross_ppp" => $ppp,
            "selling_price_ex" => $gross_price,
            "selling_price_inc" => $gross_price + $vat_p,

            "discount" => optional($price)['discount'] ?? [],
            "margins" => optional($price)['margins'] ?? [],
        ];
    }

    /**
     * Handles the price layout.
     *
     * @param array|null $prices The price array.
     * @return array The processed price array.
     */
    private function handelPricesLayout(
        ?array $prices = []
    ): array
    {
        $results = [];

        collect($prices)->each(function ($item) use (&$results) {
            $results[] = [
                "id" => optional($item)['id'],
                "p" => optional($item)['p'] ?? 0,
                "pm" => optional($item)['pm'],
                "dlv" => $this->getBusinessDay($this->category, optional($item)['dlv']),
                "ppp" => optional($item)['ppp'] ?? 0,
                "qty" => optional($item)['qty'] ?? 0,
                "vat" => optional($item)['vat'] ?: 0,
                "vat_p" => optional($item)['vat_p'] ?? 0,
                "vat_ppp" => optional($item)['vat_ppp'] ?? 0,
                "profit" => optional($item)['profit'] ?? 0,

                "gross_price" => optional($item)['gross_price'] ?? 0,
                "gross_ppp" => optional($item)['gross_ppp'] ?? 0,
                "selling_price_ex" => optional($item)['selling_price_ex'] ?? 0,
                "selling_price_inc" => optional($item)['selling_price_inc'] ?? 0,

                "discount" => optional($item)['discount'] ?? [],
                "margins" => optional($item)['margins'] ?? [],
            ];
        });

        return $results;
    }


    /**
     * Retrieves the business day for a specific category and delivery.
     *
     * @param array $category The category details.
     * @param array $dlv The delivery details.
     * @return array The calculated business day information.
     *
     */
    protected function getBusinessDay(
        array $category,
        array $dlv = []
    ): array
    {
        if (!optional($category)['production_days']) {
            return $dlv;
        }
        if ($category['countries']) {
            $region = Str::lower("{$category['countries'][0]['iso2']}-{$category['countries'][0]['un_code']}");
            $baseList = $region; // or region such as 'us-il'
        } else {
            $baseList = 'nl-national'; // or region such as 'us-il'
        }
        $days = optional($dlv)['days'] ?? 0;
        $extraDays = 0;
        $i = 0;
        $working_day = true;
        $additionalHolidays = [];
        $extraDaysWorks = [];
        $now = Carbon::now();
        $isoDay = Str::lower($now->copy()->locale('en')->isoFormat('ddd'));
        $start_day = collect($category['production_days'])->first(fn($item) => ($item['day'] === $isoDay));
        $day_off = collect($category['production_days'])->pluck('active', 'day');

        $days = (strtotime($start_day['deliver_before']) >= time()) && $start_day['active'] ? $days ?? -1 : $days;
        while ($working_day) {
            $date = Carbon::now()->addDays($extraDays);
            if (!$day_off[Str::lower($date->locale('en')->isoFormat('ddd'))]) {
                $additionalHolidays[$date->isoFormat('ddd-D')] = $date->isoFormat('M-D');
            } else {
                /**
                 * Fixme if data in calendar Remove it from  $extraDaysWorks Array
                 * we don't have calendar yet
                 */
                $extraDaysWorks[$date->isoFormat('ddd-D')] = $date->isoFormat('M-D');
                $i++;
            }
            $extraDays++;
            if ($i > $days) {
                $working_day = false;
            }
        }
        BusinessDay::enable('Illuminate\Support\Carbon', $baseList, $additionalHolidays, $extraDaysWorks);
        $deliver_at = $now::addBusinessDay($days);
        return [
            'days' => $days,
            'day' => $deliver_at->isoFormat('D'),
            'day_name' => $deliver_at->isoFormat('dddd'),
            'month' => $deliver_at->isoFormat('MMMM'),
            'year' => $deliver_at->isoFormat('YYYY'),
            'actual_days' => $deliver_at->diffInDays(Carbon::today())
        ];
    }

    /**
     * Handles the calculation layout.
     *
     * @param array|null $calculations The calculation array.
     * @return array The processed calculation array.
     * @throws ValidationException
     */
    private function handelCalculationLayout(
        ?array $calculations = []
    ): array
    {
        $results = [];
        collect($calculations)->each(function ($item) use (&$results) {
            $results[] = [
                "name" => optional($item)['name'],
                "items" => $this->handelItemsLayout(optional($item)['items']),
                "machine" => $this->handelMachineLayout(optional($item)['machine'] ?? []),
                "row_price" => optional($item)['row_price'] ?? 0,
                "duration" => $this->handelApproximatelyProductionDurationLayout(
                    optional($item)['duration'] ?? []
                ),
                "price_list" => $this->handelPriceListLayout(optional($item)['price_list'] ?? []),
                "details" => $this->handelDetailsLayout(optional($item)['details'] ?? []),
                "price" => $this->handelPriceLayout(optional($item)['price'] ?? []),
            ];
        });
        return $results;
    }


    /**
     * @param array $prices
     * @return array
     */
    private function handelPriceLayout(
        array $prices
    ): array
    {
        $results = [];

        collect($prices)->each(function ($item) use (&$results) {
            $results[] = [
                "pm" => optional($item)['pm'],
                "qty" => optional($item)['qty'] ?? 0,
                "dlv" => $this->getBusinessDay($this->category, optional($item)['dlv']),
                "gross_price" => optional($item)['gross_price'] ?? 0,
                "gross_ppp" => optional($item)['gross_ppp'] ?? 0,
                "p" => optional($item)['p'] ?? 0,
                "ppp" => optional($item)['ppp'] ?? 0,
                "selling_price_ex" => optional($item)['selling_price_ex'] ?? 0,
                "selling_price_inc" => optional($item)['selling_price_inc'] ?? 0,
                "profit" => optional($item)['profit'] ?? 0,
                "discount" => optional($item)['discount'] ?? [],
                "margins" => optional($item)['margins'] ?? [],
                "vat" => optional($item)['vat'] ?? 0
            ];
        });
        return $results;
    }

    /**
     * @param array $details
     * @return array
     */
    private function handelDetailsLayout(
        array $details
    ): array
    {
        return $details;
    }

    /**
     * Handles the approximate production duration layout.
     *
     * @param array $approximately_production_duration
     * @return array The processed approximately production duration array.
     */
    private function handelApproximatelyProductionDurationLayout(
        array $approximately_production_duration
    ): array
    {
        return $approximately_production_duration;
    }

    /**
     * Handles the price list layout.
     *
     * @param array $price_list
     * @return array The processed price list array.
     */
    private function handelPriceListLayout(
        array $price_list
    ): array
    {
        return $price_list;
    }

    /**
     * Handles the color layout.
     *
     * @param array $color The color array.
     * @return array The processed color array.
     *
     * @throws ValidationException If the color is empty.
     */
    private function handelColorLayout(
        array $color
    ): array
    {
        if (empty($color)) {
            throw ValidationException::withMessages([
                'color' => [
                    __("Colors not set, Dto.")
                ]
            ]);
        }
        return [
            "run" => $color['run'],
            "runs" => $color['runs'],
            "dlv" => $color['dlv'],
            "price" => $color['price'],
            "price_list" => $color['price_list'],
            "rpm" => $color['rpm'],
        ];
    }

    /**
     * Handles the machine layout.
     *
     * @param array $machine The machine array.
     * @return array The processed machine array.
     *
     * @throws ValidationException If the machine is empty.
     */
    private function handelMachineLayout(
        array $machine
    ): array
    {
        if (empty($machine)) {
            throw ValidationException::withMessages([
                'machine' => [
                    __("Machine not set, Dto.")
                ]
            ]);
        }

        return [
            'id' => $machine['_id'],
            'tenant_id' => $machine['tenant_id'],
            'tenant_name' => $machine['tenant_name'],
            'name' => $machine['name'],
            'type' => $machine['type'],
            'unit' => $machine['unit'],
            'width' => $machine['width'],
            'height' => $machine['height'],
            'spm' => $machine['spm'],
            'price' => $machine['price'],
            'sqcm' => $machine['sqcm'],
            'ean' => $machine['ean'],
            'pm' => $machine['pm'],
            'setup_time' => $machine['setup_time'],
            'cooling_time' => $machine['cooling_time'],
            'cooling_time_per' => $machine['cooling_time_per'],
            'mpm' => $machine['mpm'],
            'divide_start_cost' => $machine['divide_start_cost'],
            'spoilage' => $machine['spoilage'],
            'wf' => $machine['wf'],
            'min_gsm' => $machine['min_gsm'],
            'max_gsm' => $machine['max_gsm'],
            'printable_frame_length_min' => $machine['printable_frame_length_min'],
            'printable_frame_length_max' => $machine['printable_frame_length_max'],
            'fed' => $machine['fed'],
            'trim_area' => $machine['trim_area'],
            'trim_area_exclude_y' => $machine['trim_area_exclude_y'],
            'trim_area_exclude_x' => $machine['trim_area_exclude_x'],
            'margin_right' => $machine['margin_right'],
            'margin_left' => $machine['margin_left'],
            'margin_top' => $machine['margin_top'],
            'margin_bottom' => $machine['margin_bottom'],
        ];
    }

    /**
     * Handles the category layout.
     *
     * @param array|null $category The category array.
     * @return array The processed category array.
     *
     * @throws ValidationException If the category is empty.
     */
    private function handelCategoryLayout(
        ?array $category = []
    ): array
    {
        if (empty($category)) {
            throw ValidationException::withMessages([
                'category' => [
                    __("Category not set, Dto.")
                ]
            ]);
        }

        return [
            'id' => optional($category)['_id'] ?? optional($category)['id'],
            'tenant_id' => optional($category)['tenant_id'],
            'tenant_name' => optional($category)['tenant_name'],
            'countries' => optional($category)['countries'],
            'name' => optional($category)['name'],
            'slug' => optional($category)['slug'],
            'display_name' => optional($category)['display_name'],
            'price_build' => optional($category)['price_build'],
            'calculation_method' => optional($category)['calculation_method'],
            'production_days' => optional($category)['production_days'],
            'start_cost' => optional($category)['start_cost'],
            'linked' => optional($category)['linked'],
            'bleed' => optional($category)['bleed'],
            'ref_id' => optional($category)['ref_id'],
            'ref_category_name' => optional($category)['ref_category_name'],
            'vat' => optional($category)['vat'],
        ];
    }

    /**
     * Handles the category layout.
     *
     * @param array|null $category The category array.
     * @return array The processed category array.
     *
     * @throws ValidationException If the category is empty.
     */
    private function handelEmptyCategoryLayout(
        ?array $category = []
    ): array
    {
        if (empty($category)) {
            throw ValidationException::withMessages([
                'category' => [
                    __("Category not set, Dto.")
                ]
            ]);
        }

        return [
            'id' => null,
            'tenant_id' => optional($category)['supplier_id'],
            'tenant_name' => optional($category)['supplier_name'],
            'countries' => optional($category)['countries'] ?? [],
            'name' => optional($category)['name'],
            'slug' => optional($category)['slug'],
            'display_name' => setDisplayName(optional($category)['display_name']),
            'price_build' => optional($category)['price_build'] ?? [],
            'calculation_method' => optional($category)['calculation_method'] ?? [
                    "collection" => false,
                    "open_product" => true,
                    "semi_calculation" => false,
                    "full_calculation" => false
                ],
            'production_days' => optional($category)['production_days'] ?? [
                    [
                        "day" => 'mon',
                        "active" => true,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'tue',
                        "active" => true,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'wed',
                        "active" => true,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'thu',
                        "active" => true,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'fri',
                        "active" => true,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'sat',
                        "active" => false,
                        "deliver_before" => '12:00'
                    ],
                    [
                        "day" => 'sun',
                        "active" => false,
                        "deliver_before" => '12:00'
                    ]
                ],
            'start_cost' => optional($category)['start_cost'] ?? 0,
            'linked' => optional($category)['linked'],
            'bleed' => optional($category)['bleed'] ?? 0,
            'ref_id' => optional($category)['ref_id'],
            'ref_category_name' => optional($category)['ref_category_name'],
            'vat' => optional($category)['vat'] ?? Settings::vat()?->value,
        ];
    }

    public function productToItems(
        null|array $products = []
    ): array
    {
        return $this->handelItemsLayoutFromProduct($products);
    }

    /**
     * Handles the layout of items from the product data.
     *
     * @param array|null $products The products data array.
     * @return array The formatted array of item details.
     */
    private function handelItemsLayoutFromProduct(
        null|array $products = []
    ): array
    {
        if (empty($products)) {
            return [];
        }

        //@todo get the options from the mongodb

        return collect($products)->map(function ($item) {
            return [
                "key_link" => optional($item)['key_link'],
                "key_id" => optional(optional($item)['box'])['_id'],
                "key_appendage" => (bool)optional(optional($item)['box'])['appendage'],
                "key_calc_ref" => optional(optional($item)['box'])['calc_ref'] ?? "other",
                'key_display_name' => optional(optional($item)['box'])['display_name'],
                "key_start_cost" => optional(optional($item)['box'])['start_cost'],
                "key_incremental" => optional(optional($item)['box'])['incremental'] ?? false,
                "key" => optional($item)['key'],
                "value_link" => optional($item)['value_link'],
                "value" => optional($item)['value'],
                "value_id" => optional(optional($item)['option'])['_id'],
                'value_display_name' => optional(optional($item)['option'])['display_name'],
                "value_dimension" => optional(optional($item)['option'])['dimension'],
                "value_dynamic" => optional(optional($item)['option'])['dynamic'] ?? optional($item)['dynamic'],
                "value_unit" => optional(optional($item)['option'])['unit'],
                "value_width" => optional(optional($item)['option'])['width'],
                "value_maximum_width" => optional(optional($item)['option'])['maximum_width'],
                "value_minimum_width" => optional(optional($item)['option'])['minimum_width'],
                "value_height" => optional(optional($item)['option'])['height'],
                "value_maximum_height" => optional(optional($item)['option'])['maximum_height'],
                "value_minimum_height" => optional(optional($item)['option'])['minimum_height'],
                "value_length" => optional(optional($item)['option'])['length'],
                "value_minimum_length" => optional(optional($item)['option'])['minimum_length'],
                "value_maximum_length" => optional(optional($item)['option'])['maximum_length'],
                "value_start_cost" => optional(optional($item)['option'])['start_cost'],
            ];
        })->toArray();
    }

    /**
     * Handles the product layout.
     *
     * @param array|null $items
     * @return array The processed product layout.
     *
     */
    private function handelItemsLayout(
        null|array $items = [],
        null|array $products = []
    ): array
    {
        if (empty($items) || empty($products)) {
            return [];
        }


        $products = collect($products)->first();
        return collect($items)->map(function ($item) use ($products) {
            return [
                "key" => optional($item)['key'],
                "key_divider" => optional($item)['divider'],
                "key_dynamic" => optional($item)['dynamic'],
                "key_link" => optional($item)['key_link'],
                'key_display_name' => optional(optional($item)['box'])['display_name'] ?? setDisplayName(optional(optional($item)['box'])['name']),
                "key_id" => optional(optional($item)['box'])['_id'],
                "key_appendage" => (bool)optional(optional($item)['box'])['appendage'],
                "key_calc_ref" => optional(optional($item)['box'])['calc_ref'] ?? "other",
                "key_start_cost" => optional(optional($item)['box'])['start_cost'],
                "key_incremental" => optional(optional($item)['box'])['incremental'],

                "value_link" => optional($item)['value_link'],
                'value_display_name' => optional(optional($item)['option'])['display_name'] ?? setDisplayName(optional(optional($item)['option'])['name']),
                "value" => optional($item)['value'],
                "value_id" => optional(optional($item)['option'])['_id'] ?? optional(optional($item)['option'])['id'],
                "value_dimension" => optional(optional($item)['option'])['dimension'],
                "value_unit" => optional(optional($item)['option'])['unit'],
                "value_width" => optional(optional($products)['_'])['width'],
                "value_maximum_width" => optional(optional($item)['option'])['maximum_width'],
                "value_minimum_width" => optional(optional($item)['option'])['minimum_width'],
                "value_height" => optional(optional($products)['_'])['height'],
                "value_maximum_height" => optional(optional($item)['option'])['maximum_height'],
                "value_minimum_height" => optional(optional($item)['option'])['minimum_height'],
                "value_length" => optional(optional($item)['option'])['length'],
                "value_minimum_length" => optional(optional($item)['option'])['minimum_length'],
                "value_maximum_length" => optional(optional($item)['option'])['maximum_length'],
                "value_sides" => optional(optional($products)['_'])['sides'],
                "value_pages" => optional(optional($products)['_'])['pages'],
                "value_start_cost" => optional(optional($item)['option'])['start_cost'],
                "value_option_calc_ref" => optional($item)['option_calc_ref'],
                "value_runs" => collect(optional(optional($item)['option'])['runs'])->filter(fn($item) => $item['category_id'] === $this->category['id'])->first(),
                "value_calculation_method" => optional(optional($item)['option'])['calculation_method'],
                "value_dynamic_type" => optional(optional($item)['option'])['dynamic_type'],
                "value_dynamic" => optional(optional($item)['option'])['dynamic'],
                "value_additional" => optional(optional($item)['option'])['additional'],

            ];
        })->toArray();
    }

    /**
     * @param mixed $product
     * @param mixed $item
     * @return array
     */
    private function getItemsFromCustom(
        mixed $product,
        mixed $item
    ): array
    {

        return collect($item->variation)->map(function ($variation) use ($product) {
            $box = Box::where('row_id', optional(optional($variation)['variation'])['box_id'])->first();
            $option = Option::where('row_id', optional(optional($variation)['variation'])['option_id'])->first();

            return [
                "key_divider" => null,
                "key_dynamic" => false,
                "value_option_calc_ref" => null,
                "value_runs" => null,
                "value_calculation_method" => null,
                "value_dynamic_type" => null,
                "value_additional" => [],
                "key_link" => null,
                "key_id" => $box->row_id,
                "key_appendage" => $box->appendage,
                "key_display_name" => setDisplayName($box?->name),
                "key_calc_ref" => "other",
                "key_start_cost" => 0,
                "key_incremental" => $box->incremental,
                "key" => $box->name,
                "value_link" => null,
                "value" => $option->name,
                "value_id" => optional(optional($variation)['variation'])['option_id'],
                "value_display_name" => setDisplayName($option->name),
                "value_dimension" => null,
                "value_dynamic" => false,
                "value_unit" => null,
                "value_width" => $option->width,
                "value_maximum_width" => $option->max,
                "value_minimum_width" => $option->min,
                "value_sides" => $option->sides,
                "value_pages" => $option->pages,
                "value_height" => $option->height,
                "value_maximum_height" => $option->max,
                "value_minimum_height" => $option->max,
                "value_length" => $option->length,
                "value_minimum_length" => $option->max,
                "value_maximum_length" => $option->max,
                "value_start_cost" => optional(optional($variation)['variation'])['price'] ?? 0,
            ];
        })->toArray();

    }

    /**
     * Handles the product layout.
     *
     * @param array|null $product
     * @return array The processed product layout.
     *
     */
    private function handelProductLayout(
        ?array $product = []
    ): array
    {
        return $product ?? [];
    }

    /**
     * Builds a category array with the provided name.
     *
     * @param string|null $name The name of the category.
     * @return array The constructed category information.
     * @throws ValidationException
     */
    private function buildCategory(
        ?string $name
    ): array
    {
        if (!$name) {
            throw ValidationException::withMessages([
                "category" => [
                    __("Category field is required.")
                ]
            ]);
        }
        return [
            'id' => "",
            'tenant_id' => tenant()->uuid,
            'tenant_name' => domain()->fqdn,
            'countries' => [],
            'name' => $name,
            'slug' => Str::slug($name),
            'display_name' => setDisplayName($name),
            'price_build' => [],
            'calculation_method' => [],
            'production_days' => [
                [
                    "day" => 'mon',
                    "active" => true,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'tue',
                    "active" => true,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'wed',
                    "active" => true,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'thu',
                    "active" => true,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'fri',
                    "active" => true,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'sat',
                    "active" => false,
                    "deliver_before" => '12:00'
                ],
                [
                    "day" => 'sun',
                    "active" => false,
                    "deliver_before" => '12:00'
                ]
            ],
            'start_cost' => 0,
            'linked' => null,
            'bleed' => 0,
            'ref_id' => null,
            'ref_category_name' => null,
            'vat' => Settings::vat()?->value,
        ];
    }

    /**
     * Retrieves category data based on the provided slug.
     *
     * @param string|null $slug The slug of the category.
     * @return array The processed category data retrieved.
     * @throws ValidationException If no slug is provided or if an error occurs during category retrieval.
     */
    private function getCategory(
        ?string $slug
    ): array
    {
        if (!$slug) {
            throw ValidationException::withMessages([
                "category" => [
                    __("Category field is required.")
                ]
            ]);
        }
        $proxy = app(CategoryService::class)->obtainCategory($slug);;
        if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
            throw ValidationException::withMessages([
                'products' => [
                    __(optional($proxy)['message'])
                ]
            ]);
        }

        return $this->handelCategoryLayout($proxy['data']);
    }


    /**
     * Retrieves category data based on the provided slug.
     *
     * @param string|null $slug The slug of the category.
     * @return array The processed category data retrieved.
     * @throws ValidationException If no slug is provided or if an error occurs during category retrieval.
     */
    private function getOrLayoutCategory(
        ?string $slug,
        ?string $name,
        ?string $supplier_id,
        ?string $supplier_name
    ): array
    {
        if (!$slug) {
            throw ValidationException::withMessages([
                "category" => [
                    __("Category field is required.")
                ]
            ]);
        }
        $proxy = app(CategoryService::class)->obtainCategory($slug);
        if (optional($proxy)['data']) {

            return $this->handelCategoryLayout($proxy['data']);
        }

        return $this->handelEmptyCategoryLayout([
                "name" => $name,
                "slug" => $slug,
                "supplier_id" => $supplier_id,
                "supplier_name" => $supplier_name,
            ]
        );

    }

    /**
     * Retrieves items based on the category and product list provided.
     *
     * @param string|null $category The category for which items need to be retrieved.
     * @param array $products The list of products to obtain items for.
     * @return array The processed items' layout.
     * @throws ValidationException When category or products are missing or error occurs during item retrieval.
     */
    private function getItems(
        ?string $category = null,
        array   $products = [],
        ?string $calculation_type = null,
    ): array
    {
        if (!$category) {
            throw ValidationException::withMessages([
                "category" => [
                    __("Category field is required.")
                ]
            ]);
        }

        if (empty($products)) {
            throw ValidationException::withMessages([
                'products' => [
                    __("Product field is required.")
                ]
            ]);
        }
        $proxy = app(CalculationService::class)->obtainProductItems($products, $calculation_type);
        if (optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
            throw ValidationException::withMessages([
                'products' => [
                    __(optional($proxy)['message'])
                ]
            ]);
        }

        return $this->handelItemsLayout($proxy);
    }

    /**
     * @param array $categoryData
     * @param array $productData
     * @return array
     */
    public function getItemsForExternalProduct(
        array $categoryData,
        array $productData = []
    ): array
    {
        $boops = count($productData)?
            collect($categoryData['boops'][0]['boops'])
                ->filter(fn($item) => in_array($item['id'], collect($productData)->pluck('key_id')->toArray()))->toArray():
            $categoryData['boops'][0]['boops'];

        $result = [];
        foreach ($boops as $boxData) {
            $linked = optional($boxData)['linked'];
            $boxData['_id'] = optional($boxData)['id'];
            $options = count($productData)?
                collect($boxData['ops'])->filter(fn($ops) => in_array($ops['id'], collect($productData)->pluck('value_id')->toArray()))->toArray():
                $boxData['ops'];
            foreach ($options as $optionData) {
                $result[] = [
                    "key_divider" => optional($boxData)['divider'],
                    "key_dynamic" => optional($boxData)['dynamic'],
                    "key_id" => optional($boxData)['_id'],

                    "value_option_calc_ref" =>  optional($optionData)['calc_ref'],
                    "value_runs" =>  collect(optional($optionData)['runs'])->filter(fn($item) => $item['category_id'] === $this->category['id'])->first(),
                    "value_calculation_method" => optional($optionData)['calculation_method'],
                    "value_dynamic_type" =>  optional($optionData)['dynamic_type'],
                    "value_additional" => optional($optionData)['additional'],
//
                    'box' => $boxData,
                    'option' => $optionData,
                    'key_link' => $linked,
                    'key' => $boxData['slug'],
                    'value' => $optionData['slug'],
                    'key_display_name' => optional($boxData)['display_name'],
                    "key_appendage" => optional($optionData)['key_appendage'],
                    "key_calc_ref" => optional($optionData)['key_calc_ref'],
                    "key_start_cost" => optional($optionData)['key_start_cost'],
                    "key_incremental" => optional($optionData)['key_incremental'],
                    "value_id" => optional($optionData)['value_id'],
                    'value_link' => optional($optionData)['linked'],
                    "value_dimension" => optional($optionData)['value_dimension'],
                    "value_dynamic" => optional($optionData)['value_dynamic'],
                    'value_display_name' => optional($optionData)['display_name'],
                    "value_unit" => optional($optionData)['value_unit'],
                    "value_width" => optional($optionData)['value_width'],
                    "value_sides" => optional($optionData)['value_sides'],
                    "value_pages" => optional($optionData)['value_pages'],
                    "value_maximum_width" => optional($optionData)['value_maximum_width'],
                    "value_minimum_width" => optional($optionData)['value_minimum_width'],
                    "value_height" => optional($optionData)['value_height'],
                    "value_maximum_height" => optional($optionData)['value_maximum_height'],
                    "value_minimum_height" => optional($optionData)['value_minimum_height'],
                    "value_length" => optional($optionData)['value_length'],
                    "value_minimum_length" => optional($optionData)['value_minimum_length'],
                    "value_maximum_length" => optional($optionData)['value_maximum_length'],
                    "value_start_cost" => optional($optionData)['value_start_cost'],
                ];
            }
        }

        return $this->handelItemsLayout($result);
    }

    /**
     * Handles the static method calls dynamically.
     *
     * @param string $name The name of the method being called.
     * @param array $arguments The arguments passed to the method.
     * @return mixed The result of the dynamically called method.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $method = Str::replace('from', '', Str::lower($name));
        return (new self())->{$method}(...$arguments);
    }

    /**
     * @param array|null $products
     * @return array|null
     */
    private function handelExternalProduct(?array $products): ?array
    {
        return collect($products)->map(fn($product) => [
            "key" => optional($product)['key'],
            "display_key" => optional($product)['display_key'],
            "value" => optional($product)['value'],
            "display_value" => optional($product)['display_value'],
            "_" => optional($product)['_'] ?? [],
            "divider" => optional($product)['divider'] ?? "",
            "dynamic" => optional($product)['dynamic'] ?? false,
        ])->toArray();
    }

}
