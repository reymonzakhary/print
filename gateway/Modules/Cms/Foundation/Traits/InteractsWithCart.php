<?php

namespace Modules\Cms\Foundation\Traits;

use Illuminate\Support\Facades\DB;
use App\Foundation\Status\Status;
use App\Models\Tenants\Box;
use App\Models\Tenants\CartVariation;
use App\Models\Tenants\Context;
use App\Models\Tenants\Item;
use App\Models\Tenants\Option;
use App\Models\Tenants\Order as TenantsOrder;
use App\Repositories\ItemRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Str;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait InteractsWithCart
{
    public function addToCart()
    {
        if (optional($this->request->__data)['type'] === 'print') {
            $this->addPrintProductToCart();
            return redirect($this->accepted($this->callback_uri)? $this->callback_uri : $this->currentResource->uri);
        }
        $product = Product::where('row_id', $this->product_id??$this->request->product_id)->with('sku')->first();

        if ($product?->sku) {
            $qty = $this->accepted($this->request->qty)? $this->request->qty : 1;

            $this->cartInterface->add($product, $product->sku, $product->row_id, [], $qty);
        }

        // refresh the cache
        $this->refreshCartCache();

        return redirect($this->accepted($this->callback_uri)? $this->callback_uri : $this->currentResource->uri);
    }

    /**
     *
     */
    public function addPrintProductToCart() {

        if (is_string($this->request->product)) {
            $product = json_decode($this->request->product, true);
        } else if (is_array($this->request->product) && !optional($this->request->product)['prices']) {
            return;
        }

        $product = [
            'category_id' => optional($this->request->__data)['category_id'],
            'category_name' => optional($this->request->__data)['category_name'],
            'category_slug' => optional($this->request->__data)['category_slug'],
            'object' => $product['product']['object'],
            'prices' => $product
        ];

        $item_id = $this->cartInterface->addPrintingProduct($product, $this->request->quantity);

        $this->refreshCartCache();

        return $item_id;
    }

    /**
     * @param CartVariation $item
     * @return array
     */
    protected function formatItem(
        $item
    ): array
    {
        $requestedItem = $this->getRequestedItemFromRequest($item->id);

        return [
            'sku_id' => $item->sku_id,
            'product' => $this->collectCustomProduct($item),
            'reference' => $item->reference,
            'delivery_separated' => $this->accepted(optional($requestedItem)['delivery_separated']),
            // 'vat_id' => $this->request->vat_id,
            'st' => Status::NEW,
            'supplier_id' => $this->request->tenant->uuid,
            'supplier_name' => $this->request->hostname->fqdn,
        ];
    }

    protected function collectCustomProduct(
        $item
    ): array
    {
        $price = 0;
        if ($item?->variation && count($item->variation)) {
            $qty = $item->qty > 0 ? $item->qty : 1;

            $sku = Sku::find((int)$item->sku_id)->with('product')->first();
            $product = Product::where('row_id',(int)$item->product_id)->first();
            $price += (int)$price;
            $box = Box::where('row_id', $item->variation['variation']['box_id'])->first();
            $option = Option::where('row_id',$item->variation['variation']['option_id'])->first();
            return [
                'custom' => true,
                'hasVariation' => true,
                'object' => [
                        'key_id' => $box->id,
                        'key' => $box->name,
                        'key_link' => $box->id,
                        'value_link' => $option->id,
                        'display_key' => setDisplayName($box->name),
                        'value_id' => $item->variation['variation']['option_id'],
                        'value' => $option->name,
                        'display_value' => setDisplayName($option->name)
                ],
                'prices' => [
                    'id' => $sku->id,
                    'tables' => [
                        'p' => $qty * $price,
                        'pm' => '',
                        'dlv' => [],
                        'ppp' => $price,
                        'qty' => $qty,
                        'resale_p' => $qty * $price,
                        'resale_ppp' => $price,
                        'display_p' => $qty * $price,
                        'display_ppp' => $price,
                        'display_resale_p' => $qty * $price,
                        'display_resale_ppp' => $price,
                    ],
                    'host_id' => hostname()->host_id,
                    'created_at' => null,
                    'supplier_id' => tenant()->uuid,
                    'supplier_name' => tenant()->uuid,
                    'supplier_product' => 'tenant()->uuid'
                ],
                'variations' => $item?->variation,
                'product_id' => $product->row_id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'category_id' => $product->category->id ?? '0',
                'category_name' => $product->category->name . ' - ' . $product->name ?? $product->name,
                'category_slug' => $product->category->slug ?? $product->slug,
            ];
        } else {
            $sku = Sku::where('id', $item->sku_id)->with('product')->first();
            $product = Product::find((int)$item->product_id);
            $price = $sku?->price?->amount();
            $qty = $item->qty > 0 ? $item->qty : 1;
            return [
                'custom' => true,
                'hasVariation' => false,
                'object' => [],
                'prices' => [
                    'id' => $sku->id,
                    'tables' => [
                        'p' => $price * $qty,
                        'pm' => '',
                        'dlv' => [],
                        'ppp' => $price,
                        'qty' => $qty,
                        'resale_p' => $price * $qty,
                        'resale_ppp' => $price,
                        'display_p' => $price * $qty,
                        'display_ppp' => $price,
                        'display_resale_p' => $price * $qty,
                        'display_resale_ppp' => $price,
                    ],
                    'host_id' => hostname()?->host_id,
                    'created_at' => null,
                    'supplier_id' => tenant()->uuid,
                    'supplier_name' => tenant()->uuid,
                    'supplier_product' => hostname()?->fqdn
                ],
                'variations' => $item?->variation,
                'product_id' => $product->row_id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'category_id' => $product->category->id ?? '0',
                'category_name' => $product->category->name . ' - ' . $product->name ?? $product->name,
                'category_slug' => $product->category->slug ?? $product->slug,
            ];
        }
    }

    /**
     *
     * @return void
     */
    private function refreshCartCache()
    {
        Cache::put('cartContents', $this->cartInterface->contents(), 3600);
        $this->cartContents = Cache::get('cartContents');
    }

    public function removeFromCart()
    {
        $product = CartVariation::findOrFail($this->item_id);
        if ($product->delete()) {
            $product->queues()->where('queues.id', optional(optional($product->variation)['blueprint'])['queue_id'])->delete();

            // refresh the cache
            $this->refreshCartCache();
        }
        return redirect($this->callback_uri??$this->getCurrentResource()->uri);
    }

    public function emptyCart()
    {
        if (!$this->cartInterface->isEmpty()){
            $this->cartInterface->clean();

            // empty the cache
            $this->refreshCartCache();
        }
        return redirect($this->callback_uri??$this->getCurrentResource()->uri);
    }

    /**
     *
     */
    public function validateItemBeforeCheckout(array $item)
    {
        return Validator::make($item, [
            'product' => 'required|array',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|min:3|max:255',
            'product.category_id' => 'required|string',
            'product.category_name' => 'required|string',
            'product.category_slug' => 'required|string',
            'product.object' => 'required|array',
            'product.prices' => 'required|array',
            'product.prices.supplier_id' => 'required|uuid',
            'product.prices.supplier_name' => 'required',
            'product.prices.tables' => 'required|array',
            'product.prices.tables.pm' => 'nullable|string',
            'product.prices.tables.qty' => 'required|integer',
            'product.prices.tables.dlv' => 'required|array',
            'product.prices.tables.dlv.title' => 'nullable|string',
            'product.prices.tables.dlv.days' => 'required|numeric',
            'product.prices.tables.p' => 'required',
            'delivery_pickup' => [
                'nullable',
                'boolean'
            ],
            'delivery_separated' => [
                'nullable',
                'required_if:delivery_pickup,false',
            ],
            'addresses' => [
                'array',
                'required_if:delivery_separated,1',
            ],
            'address' => [
                'nullable',
                'integer',
                'exists:addresses,id',
                'required_if:delivery_separated,0',
            ]
        ]);
    }

    /**
     *
     */
    public function checkout()
    {
        $callback_uri = optional($this->request->__data)['callback_uri']??'/';
        $onSuccessRedirect = optional($this->request->__data)['onSuccessRedirect'];

        if (!auth()->user() || $this->cartInterface->contents()->isEmpty()) {
            return redirect($callback_uri);
        }

        try {
            DB::beginTransaction();

            $context = Context::firstWhere('name', 'web');
            $order = new OrderRepository(new TenantsOrder());
            $order = $order->create([
                'type' => true,
                'created_from' => $this->created_from ?? $context->name,
                'ctx_id' => $this->created_from ?? $context->id,
                'delivery_pickup' => $this->accepted($this->request->delivery_pickup),
                'delivery_multiple' => $this->accepted($this->request->delivery_multiple),
                'note' => $this->request->note,
                'st' => Status::NEW,
                'user_id' => auth()->user()?->id
            ]);

            $this->cartInterface->contents()->each(function ($cartVariation) use ($order) {
                $createdOrderItem = is_numeric($cartVariation->sku_id)?
                    $this->createCustomProductOrder($order, $cartVariation)
                    : $this->createPrintProductOrder($order, $cartVariation);

                if ($createdOrderItem) {
                    $cartVariation->delete();
                }
            });

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;

        } finally {

            Cache::forget('cartContents');
            return redirect( $onSuccessRedirect ?? $callback_uri );
        }
    }

    /**
     * @param CartVariation $cartVariation
     *
     * @return bool|Item
     */
    public function createCustomProductOrder($order, CartVariation $cartVariation)
    {
        $requestedItem = $this->getRequestedItemFromRequest($cartVariation->id);

        $item = $order->items()->create($this->formatItem($cartVariation));

        if (optional($requestedItem)['address']) {
            $item->addresses()->attach((int) optional($requestedItem)['address']);
        }

        return $item;
    }

    /**
     * @param CartVariation $cartVariation
     *
     * @return bool|Item
     */
    public function createPrintProductOrder($order, CartVariation $cartVariation): bool|Item
    {
        $item = new ItemRepository(new Item());

        $requestedItem = $this->getRequestedItemFromRequest($cartVariation->id);

        $supplier_name = tenant()->hostnames->first()->fqdn;
        $supplier_id = tenant()->uuid;

        $price = optional($cartVariation->variation)['prices'];

        $data = [
            'product' => [
                'category_id' => $cartVariation->variation['category_id'],
                'category_name' => $cartVariation->variation['category_name'],
                'category_slug' => $cartVariation->variation['category_slug'],
                'object' => $cartVariation->variation['object'],
                'prices' => array_merge([
                    'supplier_name' => $supplier_name,
                    'supplier_id' => $supplier_id
                ], $price),
                'supplier_name' => $supplier_name,
                'supplier_id' => $supplier_id,
            ],
            'delivery_separated' => $this->accepted(optional($requestedItem)['delivery_separated']),
            'note' => optional($requestedItem)['note'],
            'reference' => optional($requestedItem)['reference'],
            'st' => Status::NEW,
            'addresses' => optional($requestedItem)['addresses']??[],
            'address' => optional($requestedItem)['address']
        ];

        $validator = $this->validateItemBeforeCheckout($data);

        if ($validator->fails()) {

            return false;
        }

        $item->order = $order;

        $orderItem = $item->create($data);

        if (optional($requestedItem)['address']) {
            $orderItem->addresses()->attach((int) optional($requestedItem)['address']);
        }

        return $orderItem;
    }

    /**
     * @param integer $id cartVariation id
     *
     * @return array
     */
    private function getRequestedItemFromRequest($id): array
    {
        $item = [];
        collect($this->request->all())->each(function ($v, $k) use ($id, &$item) {
            if (str_starts_with($k, 'item-'.$id)) {
                $item[Str::replace('item-'.$id.'-', '', $k)] = $v;
            }
        });
        return $item;
    }
}
