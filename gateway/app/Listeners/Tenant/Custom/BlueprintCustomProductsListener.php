<?php

namespace App\Listeners\Tenant\Custom;

use App\Blueprint\Actions\DownloadFilesAction;
use App\Blueprint\Services\BluePrintServices;
use App\Events\Tenant\Cart\RemoveFileEvent;
use App\Events\Tenant\Custom\BlueprintCustomProductsEvent;
use App\Foundation\Status\Status;
use App\Models\Tenants\Box;
use App\Models\Tenants\CartVariation;
use App\Models\Tenants\Media\FileManager;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use App\Models\Tenants\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BlueprintCustomProductsListener implements ShouldQueue
{
    private Order $order;
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
        $this->createOrder($event->user, $event->request);
        $this->request = [
            'uuid' => $event->uuid,
            'hostId' => $event->hostId,
            'fqdn' => $event->host_fqdn
        ];
        $this->user = $event->user;

        collect(
            $event->cart->cartVariations()->where('st', '<>', Status::PENDING)->get()
        )->map(function ($cart_Variation) use ($event) {
            $sku = $cart_Variation->sku;
            $product = $cart_Variation->sku->product;
            $event->request->merge([
                'product' => $product,
                'sku' => $sku,
                'resolution' => 'production',
                'quantity' => $cart_Variation->qty,
                'tenant' => (object)[
                    'uuid' => $event->uuid,
                    'hostId' => $event->hostId,
                    'fqdn' => $event->host_fqdn,
                    'user' => $event->user
                ]
            ]);
            (bool)$product->blueprint->first()
                ? $this->withBlueprint($event->request, $cart_Variation, $event->cart)
                : $this->withoutBlueprint($event->request, $cart_Variation);
        });
        $event->cart->cartVariations()->delete();
    }

    public function withBlueprint(mixed $request, $item, $cart)
    {
        (new DownloadFilesAction())->handle($request, $item);
        app(BluePrintServices::class)->handle($request, $item, $cart->uuid);
        switchSupplier($this->request['uuid']);
        $this->resultsAction = optional(optional($request['ResultsAction'])['attachments'])[0];
        $this->init($request, $item);
        unset(optional($request)['ResultsAction']);
        event(new RemoveFileEvent($request->filesCollections));
    }

    public function withoutBlueprint(mixed $request, $item)
    {
        $this->init($request, $item);
    }

    /**
     * @param $request
     * @param $item
     * @return JsonResponse|void
     */
    public function init(
        $request,
        $item,
    )
    {
        $this->attachItems($this->prepareOrderPayload($request, $item), $request, $item);

        if (count($this->errors) > 0) {
            return response()->json([
                "message" => __("We could not add your order!"),
                "errors" => $this->errors['errors'],
                "status" => $this->errors['status']
            ], $this->errors['status']
            );
        }
    }

    /**
     * @param User  $user
     * @param mixed $request
     * @return void
     */
    private function createOrder(
        User  $user,
        mixed $request
    )
    {

        $inputs = $request->toArray();
        $inputs['price'] = 0;
        $inputs['user_id'] = $user->id;
        $inputs['st'] = Status::NEW;
        $inputs['reference'] = $request?->reference ?? null;

        $this->order = $user->orders()->create($inputs);

        $this->order->delivery_address()->sync([$inputs['address'] => [
            'type' => $inputs['address_type'],
            'full_name' => $inputs['address_full_name'],
            'company_name' => $inputs['address_company_name'],
            'phone_number' => $inputs['address_phone_number'],
            'tax_nr' => $inputs['address_tax_nr']
        ]]);
    }

    /**
     * @param array   $items
     * @param Request $request
     * @return mixed
     */
    private function attachItems(
        array   $item,
        Request $request,
                $cart
    )
    {
        $cart = CartVariation::find($cart->id);
        $medias = $cart->userMedia($this->user->id)->get();
        $media = $medias?->filter(fn($m) => $m->name == $this->resultsAction['name']) ?? collect([]);
        $item['st'] = Status::NEW;
        $item = Item::create($item);
        $media->map(function ($i) use ($item) {
            $newLocation = "/orders/{$this->order->id}/items/{$item->id}/";
            cloneData(
                $i->disk,
                $this->request['uuid'] . "/" . $i->path . '/' . $i->name,
                'tenancy',
                $this->request['uuid'] . '/' . $newLocation . '/' . $i->name
            );

            FileManager::create([
                'user_id' => $this->user->id,
                'model_type' => get_class($item),
                'model_id' => $item->id,
                'name' => $i['name'],
                'group' => $i['group'],
                'disk' => 'tenancy',
                'path' => $newLocation,
                'ext' => $i['ext'],
                'type' => $i['type'],
                'showing_columns' => $i['showing_columns'],
                'size' => $i['size'],
                'collection' => $i['collection'],
                'external' => $i['external'],
            ]);
        });

        $medias->map(function ($i) {
            $i->delete();
        });
        $this->order->items()->save($item, [
            'qty' => $item['qty'],
            'shipping_cost' => null
        ]);

        return response()->json([
            'messages' => __('Order has been placed successfully'),
            'meta' => [
                'order_id' => $this->order->id
            ]
        ]);
    }

    /**
     * @param $request
     * @param $cart_Variation
     * @return array
     */
    private function prepareOrderPayload($request, $cart_Variation): array
    {
        return $cart_Variation->sku_id
            ? $this->customProduct($cart_Variation, $request)
            : $this->mongo($cart_Variation, $request);
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

    public function failed(BlueprintCustomProductsEvent $event, $exception)
    {
        env('APP_ENV') === 'local' ? dd($exception, $event) : Log::debug($exception);
    }
}
