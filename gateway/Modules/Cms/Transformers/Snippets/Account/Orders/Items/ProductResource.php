<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders\Items;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'object' => $this->getObject(optional($this)['object']),
            'prices' => ProductPriceResource::make(optional($this)['prices']),
            'category_id' => optional($this)['category_id'],
            'category_name' => optional($this)['category_name'],
            'category_slug' => optional($this)['category_slug'],
        ];
    }

    protected function getObject($object)
    {
        return collect(optional($this)['object'])->map(fn($v, $k) => [
            "key" => optional($v)['key'],
            "value" => optional($v)['value'],
            "box_id" => optional($v)['box_id'],
            "key_link" => optional($v)['key_link'],
            "option_id" => optional($v)['option_id'],
            "value_link" => optional($v)['value_link'],
            "display_key" => getDisplayName(optional($v)['display_key'], Str::lower(request()->get('iso'))),
            "display_value" => getDisplayName(optional($v)['display_value'], Str::lower(request()->get('iso')))
        ])->toArray();
    }
}
