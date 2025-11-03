<?php

namespace App\Http\Controllers\Tenant\Mgr\Cart;

use App\Cart\Cart;
use App\Cart\Contracts\CartContractInterface;
use App\Cart\Contracts\CheckoutContractInterface;
use App\Events\Tenant\Order\CreatedItemOrderEvent;
use App\Foundation\Media\FileManager;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CheckoutRequest;
use App\Models\Tenants\Box;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{

    private array $errors = [];

    /**
     * @var order|null object
     */
    private mixed $order;

    /**
     * Handle the incoming request.
     *
     * @param CheckoutRequest $request
     * @param CheckoutContractInterface $checkout
     * @return Collection
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function __invoke(
        CheckoutRequest           $request,
        CheckoutContractInterface $checkout
    ): Collection
    {
        return $checkout->process();
    }

    /**
     * @param CheckoutRequest $request
     * @param CartContractInterface $cart
     * @return JsonResponse|mixed
     */
    public function init(
        CheckoutRequest       $request,
        CartContractInterface $cart
    ): mixed
    {
        if (!$cart->isEmpty()) {

            $items = $this->prepareOrderPayload($cart->contents(), $request);
            $order = $this->createOrder($request, $request->validated(), $cart)
                ->attachItems($items, $request, $cart);

            if (count($this->errors) > 0) {
                return response()->json([
                    "message" => __("We could not add your order!"),
                    "errors" => $this->errors['errors'],
                    "status" => $this->errors['status']
                ], $this->errors['status']
                );
            }
            $cart->empty();
            return $order;
        }

        return response()->json([
            'message' => __('Your cart is empty.'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param Request $request
     * @param array   $inputs
     * @param Cart    $cart
     * @return $this
     */
    protected function createOrder(
        Request $request,
        array   $inputs,
        Cart    $cart
    ): self
    {

        try {

            $this->order = $request->user()->orders()->create(
                array_merge(
                    $inputs, [
                        'price' => 0,
//                        'price' => $cart->subtotal()->amount(),
                        "user_id" => $request->user()->id,
                        'st' => 302,
                        'reference' => $request?->reference ?? null
                    ]
                )
            );

            $this->order->delivery_address()->sync([$inputs['address'] => [
                'type' => $inputs['address_type'],
                'full_name' => $inputs['address_full_name'],
                'company_name' => $inputs['address_company_name'],
                'phone_number' => $inputs['address_phone_number'],
                'tax_nr' => $inputs['address_tax_nr']
            ]]);
        } catch (Exception $e) {
            $this->errors = [
                'errors' => $e->getMessage(),
                'status' => $e->getCode()
            ];
        }

        return $this;
    }

    /**
     * @param array $items
     * @param Request $request
     * @param Cart $cart
     * @return JsonResponse
     */
    private function attachItems(
        array   $items,
        Request $request,
        Cart    $cart
    ): JsonResponse
    {
        $cartItems = $cart->contents();
        $user = $request->user();
        $fileManager = app(FileManager::class);
        foreach ($items as $item) {
            $cartItem = $cartItems->first(function ($i) use ($item) {
                return $i->sku_id === (int)$item['product']['prices']['id'];
            });
            $media = $cartItem?->media ?? collect([]);
            $item = Item::create(array_merge(
                $item, [
                    'st' => 302
                ]
            ));
            $media->map(function ($i) use ($item, $fileManager) {
                $fileManager->updateMedia($i['id'], $item->id, get_class($item), 'order-items');
            });

            $this->order->items()->save($item, [
                'qty' => $item['qty'],
                'shipping_cost' => null
            ]);
            event(new CreatedItemOrderEvent($this->order, $item, $user));
        }
        $cart->clean();
        return response()->json([
            'messages' => __('Order has been placed successfully'),
            'meta' => [
                'order_id' => $this->order->id
            ]
        ]);
    }

    /**
     * @param Collection $products
     * @param Request    $request
     * @return array
     */
    private function prepareOrderPayload(
        collection $products,
        Request    $request
    ): array
    {

        return collect($products)->map(function ($item) use ($request) {
            return $item->sku_id ? $this->customProduct($item, $request) : $this->mongo($item, $request);
        })->toArray();
    }

    public function customProduct($item, $request): array
    {
        return [
            'sku_id' => $item->sku_id,
            'product' => $this->vairations($item),
            'reference' => $item->reference,
            'delivery_separated' => $request->delivery_separated,
            'vat_id' => $request->vat_id,
            'st' => Status::NEW,
            'supplier_id' => tenant()->uuid,
            'supplier_name' => domain()?->fqdn,
        ];

    }

    /**
     * @param $item
     * @param Request $request
     * @return \array
     */
    public function mongo($item, Request $request): array
    {
        return [
            'product' => array_merge(
                collect($item->variation)->toArray(),
                [
                    'custom' => false,
                    'hasVariation' => false
                ]
            ),
            'reference' => $request->reference,
            'delivery_separated' => $request->delivery_separated,
            'vat_id' => $request->vat_id,
            'st' => Status::NEW,
            'supplier_id' => $item->variation['prices']['supplier_id'],
            'supplier_name' => $item->variation['prices']['supplier_name'],
        ];
    }

    /**
     * @param $item
     * @return mixed
     */
    public function vairations($item): mixed
    {
        $price = 0;
        if ($item?->variation && count($item->variation)) {
            $qty = $item->qty > 0 ? $item->qty : 1;

            $sku = Sku::find((int)$item->sku_id)->with('product')->first();
            $product = Product::find((int)$item->product_id);
            return [
                'custom' => true,
                'hasVariation' => true,
                'object' => collect($item->variation)->map(function ($variation) use ($price) {
                    $price += (int)$price;
                    $box = Box::find($variation['variation']['box_id']);
                    return [
                        'key_id' => $box->id,
                        'key' => $box->name,
                        'display_key' => setDisplayName($box->name),
                        'value_id' => $variation['variation']['option']['id'],
                        'value' => $variation['variation']['option']['name'],
                        'display_value' => setDisplayName($variation['variation']['option']['name'])
                    ];
                })->toArray(),
                'prices' => [
                    'id' => $sku->id,
                    'tables' => [
                        'p' => $qty * $price,
                        'pm' => '',
                        'dlv' => '',
                        'ppp' => $price,
                        'qty' => $qty,
                        'resale_p' => $qty * $price,
                        'display_p' => $qty * $price,
                        'display_ppp' => $price,
                        'display_resale_p' => $qty * $price,
                        'display_resale_ppp' => $price,
                    ],
                    'host_id' => domain()->host_id,
                    'created_at' => null,
                    'supplier_id' => tenant()->uuid,
                    'supplier_name' => tenant()->uuid,
                    'supplier_product' => 'tenant()->uuid'
                ],
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
                        'dlv' => '',
                        'ppp' => $price,
                        'qty' => $qty,
                        'resale_p' => $price * $qty,
                        'display_p' => $price * $qty,
                        'display_ppp' => $price,
                        'display_resale_p' => $price * $qty,
                        'display_resale_ppp' => $price,
                    ],
                    'host_id' => domain()->host_id,
                    'created_at' => null,
                    'supplier_id' => tenant()->uuid,
                    'supplier_name' => tenant()->uuid,
                    'supplier_product' => domain()->fqdn
                ],
                'category_id' => $product->category->id ?? '0',
                'category_name' => $product->category->name . ' - ' . $product->name ?? $product->name,
                'category_slug' => $product->category->slug ?? $product->slug,
            ];
        }

    }

    public function getMedia($sku)
    {
        $cartModal = \App\Models\Tenants\Cart::whereUuid(session(tenant()->uuid . '_cart_session'))->first();
        return $cartModal->getMedia('cart' . $sku['sku_id']) ?? [];
    }

}
