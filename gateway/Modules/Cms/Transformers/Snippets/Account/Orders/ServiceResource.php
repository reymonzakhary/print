<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders;

use App\Http\Resources\Media\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'display_price' => $this->formattedPrice,
            'price' => $this->price->amount(),
            'vat_id' => optional($this->pivot)->vat_id,
            'qty' => optional($this->pivot)->qty,
            'attachments' => MediaResource::collection($this->media),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
