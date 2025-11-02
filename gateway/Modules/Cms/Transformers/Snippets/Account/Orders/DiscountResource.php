<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders;

use App\Plugins\Moneys;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
            'type' => $this->type,
            'value' => $this->value,
            'display_value' => $this->type === 'percentage' ? "{$this->value} %" : ((new Moneys())->setAmount($this->value))->format(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
