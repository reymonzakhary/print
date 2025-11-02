<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders;

use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Plugins\Moneys;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Snippets\Account\AddressResource;
use Modules\Cms\Transformers\Snippets\Account\Orders\Items\ItemResource;
use Modules\Cms\Transformers\Snippets\Account\UserTeamResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'note' => $this->note,
            'invoice_nr' => $this->invoice_nr,
            'order_nr' => $this->order_nr,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'expire_at' => $this->expire_at,
            'type' => __(config('tenant_order_config.type')[$this->type]),
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
            'delivery_address' => AddressResource::make(collect($this->whenLoaded('address'))->where('pivot.type', '!=', 'invoice')->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'invoice_address' => AddressResource::make(collect($this->whenLoaded('invoice_address'))->first())->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'shipping_cost' => $this->shipping_cost,
            'display_shipping_cost' => ((new Moneys())->setAmount($this->shipping_cost))->format(),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_reference' => $this->payment_reference,
            'context' => ContextResource::make($this->whenLoaded('context'))->hide(
                optional($this->withoutChildrenFields)['context'] ?? []
            ),
            'customer' => CustomerResource::make($this->whenLoaded('orderedBy'))->hide(
                optional($this->withoutChildrenFields)['customer'] ?? []
            ),
            'created_from' => $this->created_from,
            'locked_by' => CustomerResource::make($this->whenLoaded('lockedBy'))->hide(
                optional($this->withoutChildrenFields)['lockedBy'] ?? []
            ),
            'locked_at' => $this->locked_at ?? null,
            'created_at' => Carbon::instance($this->created_at)->toISOString(true),
            'updated_at' => Carbon::instance($this->updated_at)->toISOString(true),
            'items' => ItemResource::collection($this->whenLoaded('items'))->hide(
                optional($this->withoutChildrenFields)['items'] ?? []
            ),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'total_price' => optional($this)->total_price,
            'display_total_price' => ((new Moneys())->setAmount(optional($this)->total_price))->format(),
            'subTotal_price' => optional($this)->subTotal_price,
            'display_subTotal_price' =>((new Moneys())->setAmount(optional($this)->subTotal_price))->format(),
            'services_price' => optional($this)->services_price,
            'display_services_price' => ((new Moneys())->setAmount(optional($this)->services_price))->format(),
            'vat_price' => optional($this)->vats_price,
            'display_vat_price' => ((new Moneys())->setAmount(optional($this)->vats_price))->format(),
            'items_price_array' => optional($this)->items_price_array,
            'discount' => DiscountResource::make($this->whenLoaded('discount'))->hide(
                ['id', 'created_at', 'updated_at']
            ),
            'team' => UserTeamResource::make($this->team)
        ];
    }
}
