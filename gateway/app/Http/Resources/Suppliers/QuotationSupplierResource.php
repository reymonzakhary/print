<?php

namespace App\Http\Resources\Suppliers;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Items\ProductItemResource;
use App\Http\Resources\Quotations\QuotationCustomerResource;
use App\Http\Resources\Services\QuotationServiceResource;
use App\Http\Resources\Statuses\StatusResource;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationSupplierResource extends JsonResource
{
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
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'reference' => $this->reference,
            'order_nr' => $this->order_nr,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'contract' => ContractResource::make($this->contract),
            'expire_at' => $this->expire_at,
            'type' => config('tenant_order_config.type')[$this->type],
            'status' => StatusResource::make($this->st),
            'delivery_type' => config('tenant_order_config.delivery_type')[(boolean)$this->delivery_multiple],
            'delivery_method' =>
                (boolean)$this->delivery_multiple ?
                    null :
                    config('tenant_order_config.delivery_method')
                    [$this->delivery_pickup ?? 0]
            ,
            'delivery_address' => AddressResource::make(collect($this->whenLoaded('address'))->first()),
            'invoice_address' => AddressResource::make(collect($this->whenLoaded('invoice_address'))->first()),
            'shipping_cost' => $this->shipping_cost,
            'context' => ContextResource::make($this->whenLoaded('context')),
            'customer' => QuotationCustomerResource::make($this->whenLoaded('orderedBy')),
            'created_from' => $this->created_from,
            'message' => $this->message,
            'locked_by' => QuotationCustomerResource::make($this->whenLoaded('lockedBy')),
            'locked_at' => $this->locked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => ProductItemResource::collection($this->whenLoaded('items')),
            'services' => QuotationServiceResource::collection($this->services),
            'discount' => DiscountResource::make($this->whenLoaded('discount')),
        ];
    }
}
