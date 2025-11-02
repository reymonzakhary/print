<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders\Items;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Snippets\Account\AddressResource;

class ChildrenResource extends JsonResource
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
            'qty' => $this->qty,
            'delivery_pickup' => $this->delivery_pickup,
            'shipping_cost' => $this->shipping_cost,
            'delivery_addresses' => AddressResource::make(collect($this->addresses)->first())->hide([]),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
