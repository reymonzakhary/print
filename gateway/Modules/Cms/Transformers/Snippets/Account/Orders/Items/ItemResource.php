<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders\Items;

use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Http\Resources\Tags\TagResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Snippets\Account\AddressResource;
use Modules\Cms\Transformers\Snippets\Account\Orders\DiscountResource;

class ItemResource extends JsonResource
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
            'product' => ProductResource::make($this->product),
            'vat_id' => $this->vat_id,
            'reference' => $this->reference,
            'sku' => $this->sku,
            'sku_id' => $this->sku_id,
            'has_blueprint' => $this->sk?->product?->hasBlueprint,
            'discount' => DiscountResource::make($this->whenLoaded('discount'))->hide(
                ['id', 'created_at', 'updated_at']
            ),
            'status' => StatusResource::make($this->whenLoaded('status'))->hide(
                optional($this->withoutChildrenFields)['status'] ?? ['id', 'created_at', 'updated_at']
            ),
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,

            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'qty' => optional($this->pivot)->qty,
            'delivery_separated' => (bool)$this->delivery_separated,
            'delivery_pickup' => optional($this->pivot)->delivery_pickup,
            'shipping_cost' => optional($this->pivot)->shipping_cost,

            'item_children' => ChildrenResource::collection($this->whenLoaded('children'))->hide([]),
            'delivery_date' => Carbon::now()->addDays($this->deliveryDays)->format('d-m-Y'),
            'attachments' => MediaResource::collection($this->whenLoaded('media')),
            'delivery_address' => AddressResource::make(collect($this->whenLoaded('addresses'))->first())->hide([]),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'tags' => TagResource::collection($this->whenLoaded('tags'))
        ];
    }
}
