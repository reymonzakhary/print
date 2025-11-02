<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Items\OrderItemResource;
use App\Http\Resources\Services\OrderServiceResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Http\Resources\Teams\UserTeamResource;
use App\Http\Resources\Users\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 * @package App\Http\Resources\OrderResource
 * @OA\Schema(
 * )
 */
final class OrderResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @var array
     */
    protected array $withoutChildrenFields = [];

    /**
     * Transform the resource into an array.
     * @OA\Property(format="int64", title="id", default="1158", description="ID", property="id"),
     * @OA\Property(format="int64", title="reference", default=" ", description="reference", property="reference"),
     * @OA\Property(format="int64", title="order_nr", default="0001", description="order_nr", property="order_nr"),
     * @OA\Property(format="int64", title="display_price", default="€ 0,00", description="display_price", property="display_price"),
     * @OA\Property(format="int64", title="price", default="1000", description="price", property="price"),
     * @OA\Property(format="date", title="expire_at", default="2022-03-22T14:01:16.000000Z", description="expire_at", property="expire_at"),
     * @OA\Property(format="bool", title="type", default="quotation", description="type", property="type"),
     * @OA\Property(type="array", property="status", @OA\Items(ref="#/components/schemas/StatusResource")),
     * @OA\Property(format="int64", title="delivery_type", default="single", description="delivery_type", property="delivery_type"),
     * @OA\Property(format="int64", title="delivery_method", default="delivery", description="delivery_method", property="delivery_method"),
     * @OA\Property(type="array", property="delivery_address", @OA\Items(ref="#/components/schemas/AddressResource")),
     * @OA\Property(type="array", property="invoice_address", @OA\Items(ref="#/components/schemas/AddressResource")),
     * @OA\Property(format="int64", title="shipping_cost", default="1158", description="shipping_cost", property="shipping_cost"),
     * @OA\Property(type="array", property="context", @OA\Items(ref="#/components/schemas/ContextResource")),
     * @OA\Property(type="array", property="customer", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(format="int64", title="created_from", default="mgr", description="created_from", property="created_from"),
     * @OA\Property(format="int64", title="note", default="null", description="note", property="note"),
     * @OA\Property(type="array", property="locked_by", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(format="int64", title="locked_at", default="2022-04-04T09:22:04.000000Z", description="locked_at", property="locked_at"),
     * @OA\Property(format="int64", title="created_at", default="2022-04-04T09:22:04.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="int64", title="updated_at", default="2022-04-04T09:22:04.000000Z", description="updated_at", property="updated_at"),
     * @OA\Property(type="array", property="items", @OA\Items(ref="#/components/schemas/ItemResource")),
     * @OA\Property(format="int64", title="services", default="[]", description="services", property="services"),
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        $customer = !empty($this?->customer) ?  $this->customer : OrderCustomerResource::make($this->whenLoaded('orderedBy'))->hide(
                optional($this->withoutChildrenFields)['customer'] ?? [] );



        return $this->filterFields([
            'id' => $this->id,
            'reference' => $this->reference,
            'note' => $this->note,
            'order_nr' => $this->order_nr,
            'delivery_multiple' => $this->delivery_multiple,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'expire_at' => $this->expire_at,
            'type' => __(config('tenant_order_config.type')[$this->type]),
            'status' => StatusResource::make($this->st),
            'delivery_type' => config('tenant_order_config.delivery_type')[(boolean)$this->delivery_multiple],
            'delivery_method' =>
                (boolean)$this->delivery_multiple ?
                    null :
                    config('tenant_order_config.delivery_method')
                    [$this->delivery_pickup ?? 0]
            ,
            'delivery_address' => AddressResource::make(collect($this->whenLoaded('delivery_address'))->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'invoice_address' => AddressResource::make(collect($this->whenLoaded('invoice_address'))->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'shipping_cost' => $this->shipping_cost,
            'display_shipping_cost' => moneys()->setAmount($this->shipping_cost)->format(),
            'context' => ContextResource::make($this->whenLoaded('context'))->hide(
                optional($this->withoutChildrenFields)['context'] ?? []
            ),
            'customer' => $customer,
            'created_from' => $this->created_from,
            'editing' => $this->editing,
            'locked' => $this->locked,
            'locked_by' => OrderCustomerResource::make($this->whenLoaded('lockedBy'))->hide(
                optional($this->withoutChildrenFields)['lockedBy'] ?? []
            ),
            'locked_at' => $this->locked_at ?? null,
            'created_at' => Carbon::instance($this->created_at)->toISOString(true),
            'updated_at' => Carbon::instance($this->updated_at)->toISOString(true),
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->hide(
                optional($this->withoutChildrenFields)['items'] ?? []
            ),
            'services' => OrderServiceResource::collection($this->whenLoaded('services')),
            'subTotal_price' => optional($this)->subTotal_price,
            'display_subTotal_price' => moneys()->setAmount(optional($this)->subTotal_price)->format(),
            'items_price_array' => optional($this)->items_price_array,
            'discount' => DiscountResource::make($this->whenLoaded('discount'))->hide(
                ['id', 'created_at', 'updated_at']
            ),
            'team' => UserTeamResource::make($this->team),
            'archived' => $this->archived,
            'total_price' => optional($this)->total_price,
            'display_total_price' => moneys()->setAmount(optional($this)->total_price)->format(),
            'subtotal_price' => optional($this)->subTotal_price,
            'display_subtotal_price' => moneys()->setAmount(optional($this)->subTotal_price)->format(),
            'vat_price' => optional($this)->vats_price,
            'display_vat_price' => \moneys()->setAmount(optional($this)->vats_price)->format(),
            'services_price_array' => optional($this)->order_services_price_array,
            'services_price' => optional($this)->order_services_price,
            'display_services_price' => moneys()->setAmount(optional($this)->order_services_price)->format(),
            'attachments' => $this->getMedia('attachments'),
            'author' => OrderCustomerResource::make($this->whenLoaded('author'))->hide(
                optional($this->withoutChildrenFields)['author'] ?? []
            ),
            'has_transaction' => $this->transactions()->count() > 0,
            'internal' => $this->internal
        ]);
    }

    public function system($request)
    {

        return $this->filterFields([
            'id' => $this->id,
            'reference' => $this->reference,
            'note' => $this->note,
            'order_nr' => $this->order_nr,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'expire_at' => $this->expire_at,
            'type' => config('tenant_order_config.type')[$this->type],
            'status' => StatusResource::make($this->whenLoaded('status'))->hide(
                optional($this->withoutChildrenFields)['status'] ?? []
            ),
            'delivery_type' => config('tenant_order_config.delivery_type')[(boolean)$this->delivery_multiple],
            'delivery_method' =>
                (boolean)$this->delivery_multiple ?
                    null :
                    config('tenant_order_config.delivery_method')
                    [$this->delivery_pickup ?? 0]
            ,
            'delivery_address' => AddressResource::make(collect($this->address)->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'invoice_address' => AddressResource::make(collect($this->invoice_address)->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'shipping_cost' => $this->shipping_cost,
            'display_shipping_cost' => moneys()->setAmount($this->shipping_cost)->format(),
            'context' => ContextResource::make($this->whenLoaded('context'))->hide(
                optional($this->withoutChildrenFields)['context'] ?? []
            ),
            'customer' => optional($this->properties)['customer'],
            'reseller' => '',
            'created_from' => $this->created_from,
            'locked' => $this->locked,
            'locked_by' => OrderCustomerResource::make($this->whenLoaded('lockedBy'))->hide(
                optional($this->withoutChildrenFields)['lockedBy'] ?? []
            ),
            'locked_at' => $this->locked_at ?? null,
            'created_at' => Carbon::instance($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::instance($this->updated_at)->format('Y-m-d H:i:s'),
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->hide(
                optional($this->withoutChildrenFields)['items'] ?? []
            ),
            'services' => OrderServiceResource::collection($this->services),
            'total_price' => optional($this)->total_price,
            'display_total_price' => moneys()->setAmount(optional($this)->total_price)->format(),
            'subtotal_price' => optional($this)->subTotal_price,
            'display_subtotal_price' => moneys()->setAmount(optional($this)->subTotal_price)->format(),
            'vat_price' => optional($this)->vats_price,
            'display_vat_price' => moneys()->setAmount(optional($this)->vats_price)->format(),
            'items_price_array' => optional($this)->items_price_array,
            'services_price_array' => optional($this)->order_services_price_array,
            'services_price' => optional($this)->order_services_price,
            'display_services_price' => moneys()->setAmount(optional($this)->order_services_price)->format(),
            'attachments' => $this->getMedia('attachments'),
            'author' => OrderCustomerResource::make($this->whenLoaded('author'))->hide(
                optional($this->withoutChildrenFields)['author'] ?? []
            ),
        ]);
    }

    /**
     * @param mixed $resource
     *
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection(
        mixed $resource
    ): mixed
    {
        return tap(new OrderResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Set the keys that are supposed to be filtered out from children tables.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hideChildren(
        array $fields
    ): self
    {
        $this->withoutChildrenFields = $fields;

        return $this;
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hide(
        array $fields
    ): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
