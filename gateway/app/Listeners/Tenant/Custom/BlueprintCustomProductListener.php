<?php

namespace App\Listeners\Tenant\Custom;

use App\Blueprint\Actions\CleanFilesActions;
use App\Blueprint\Actions\DownloadFilesAction;
use App\Blueprint\Services\BluePrintServices;
use App\Events\Tenant\Cart\RemoveFileEvent;
use App\Events\Tenant\Custom\BlueprintCustomProductEvent;
use App\Events\Tenant\Order\CreatedItemOrderEvent;
use App\Foundation\Media\FileManager;
use App\Foundation\Status\Status;
use App\Models\Tenants\Box;
use App\Models\Tenants\Cart;
use App\Models\Tenants\CartVariation;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use App\Models\Tenants\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BlueprintCustomProductListener implements ShouldQueue
{
    private mixed $order = Order::class;
    private ?User $user;
    private $request;
    private $resultsAction;
    private array $errors = [];

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $event->request->merge([
            'resolution' => 'production',
            'tenant' => (object)[
                'uuid' => $event->uuid,
                'hostId' => $event->hostId,
                'fqdn' => $event->host_fqdn
            ]
        ]);

        $this->request = [
            'uuid' => $event->uuid,
            'hostId' => $event->hostId,
            'fqdn' => $event->host_fqdn
        ];
        $this->user = $event->user;
        (new DownloadFilesAction())->handle($event->request, $event->cartVar);
        app(BluePrintServices::class)->handle($event->request, $event->cartVar, $event->cart_id,);
        switchSupplier($this->request['uuid']);
        $this->resultsAction = optional(optional($event->request['ResultsAction'])['attachments'])[0];
        $this->init($event->request, collect([CartVariation::find($event->cartVar->id)]), $event->cart_id);
        event(new RemoveFileEvent($event->request->filesCollections));
        CartVariation::find($event->cartVar->id)->delete();

    }

    public function init(
        $request,
        $item,
        $cart_id
    )
    {
        $card = Cart::where('uuid', $cart_id)->first();
        $items = $this->prepareOrderPayload($item, $request);
        $order = $this->createOrder($request, $request->toArray())
            ->attachItems($items, $request, $item);
        if (count($this->errors) > 0) {
            return response()->json([
                "message" => __("We could not add your order!"),
                "errors" => $this->errors['errors'],
                "status" => $this->errors['status']
            ], $this->errors['status']
            );
        }
        return $order;
    }

    /**
     * @param Request $request
     * @param array   $inputs
     * @return $this
     */
    private function createOrder(
        Request $request,
        array   $inputs
    ): self
    {
        $this->order = $this->user->orders()->create(
            array_merge(
                $inputs, [
                    'price' => 0,
//                        'price' => $cart->subtotal()->amount(),
                    "user_id" => $this->user->id,
                    'st' => 302,
                    'reference' => $request?->reference ?? null
                ]
            )
        );

        $this->order->address()->sync([$inputs['address'] => [
            'type' => $inputs['address_type'],
            'full_name' => $inputs['address_full_name'],
            'company_name' => $inputs['address_company_name'],
            'phone_number' => $inputs['address_phone_number'],
            'tax_nr' => $inputs['address_tax_nr']
        ]]);


        return $this;
    }

    /**
     * @param array   $items
     * @param Request $request
     * @return mixed
     */
    private function attachItems(
        array   $items,
        Request $request,
                $cart
    )
    {
        $cartItems = $cart;
//        switchSupplier($request->tenant->uuid);
        $user = $this->user;
        $fileManager = app(FileManager::class);
        foreach ($items as $item) {

            $cartItem = $cartItems->first(function ($i) use ($item) {
                return $i->sku_id === (int)$item['product']['prices']['id'];
            });
            $medias = $cartItem->media;
            /**
             * @todo mast fix this line
             */
            //            $media = $medias?->filter(fn($m)=>Str::startsWith($m->collection, 'production-')) ?? collect([]);
            $media = $medias?->filter(fn($m) => $m->name == $this->resultsAction['name']) ?? collect([]);
            $media_ids = $media->pluck('id')->toArray();

            $item = Item::create(array_merge(
                $item, [
                    'st' => 302
                ]
            ));
            $media->map(function ($i) use ($item, $fileManager) {
                $newLocation = "/orders/{$this->order->id}/items/{$item->id}/";
                cloneData(
                    $i->disk,
                    $this->request['uuid'] . "/" . $i->path . '/' . $i->name,
                    'tenancy',
                    $this->request['uuid'] . '/' . $newLocation . '/' . $i->name
                );
                $fileManager->updateMedia($i['id'], $item->id, get_class($item), 'order-items', $newLocation, 'tenancy');
            });
            $medias->map(function ($i) use ($media_ids, $request) {
                if (!in_array($i->id, $media_ids) && optional($request)->resolution === 'production') {
                    $i->delete();
                }
            });
            $this->order->items()->save($item, [
                'qty' => $item['qty'],
                'shipping_cost' => null
            ]);
            event(new CreatedItemOrderEvent($this->order, $item, $user));

        }

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
    )
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
            'supplier_id' => $this->request['uuid'],
            'supplier_name' => $this->request['fqdn'],
        ];

    }

    /**
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
    public function vairations($item)
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
                    'host_id' => $this->request['hostId'],
                    'created_at' => null,
                    'supplier_id' => $this->request['uuid'],
                    'supplier_name' => $this->request['fqdn'],
                    'supplier_product' => $this->request['fqdn']
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
                    'host_id' => $this->request['uuid'],
                    'created_at' => null,
                    'supplier_id' => $this->request['uuid'],
                    'supplier_name' => $this->request['fqdn'],
                    'supplier_product' => $this->request['fqdn']
                ],
                'category_id' => $product->category->id ?? '0',
                'category_name' => $product->category->name . ' - ' . $product->name ?? $product->name,
                'category_slug' => $product->category->slug ?? $product->slug,
            ];
        }

    }

    public function failed(BlueprintCustomProductEvent $event, $exception)
    {
        app(CleanFilesActions::class)->handle($event->request, [], [], $event->cartVar);
    }
}
