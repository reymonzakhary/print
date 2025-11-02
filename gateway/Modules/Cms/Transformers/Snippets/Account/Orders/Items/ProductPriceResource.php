<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders\Items;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        if (optional($this)) {
            return [
                'id' => optional($this)['id'],
                'tables' => ProductPriceTableResource::make($this['tables']),
                'supplier_product' => optional($this)['supplier_product'],
                'created_at' => optional($this)['created_at'],
                'supplier_id' => optional($this)['supplier_id'],
                'supplier_name' => optional($this)['supplier_name'],
                'website_id' => optional($this)['website_id']
            ];
        }

        return [];
    }
}
