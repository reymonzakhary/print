<?php

namespace App\Http\Resources\Products;

use App\Http\Resources\Options\OptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|AnonymousResourceCollection
     */
    public function toArray($request)
    {
        if ($this->resource instanceof Collection) {
            return self::collection($this->resource);
        }

        return [
            'id' => $this->option->row_id,
            'name' => $this->name,
//            'option_id' => $this->option->id,
            'sort' => $this->sort,
            'price' => $this->price->amount(),
            'display_price' => $this->price->format(),
            'sale_price' => $this->sale_price,
            'incremental' => $this->incremental,
            'published' => $this->published,
            'override' => $this->override,
            'free' => $this->free,
            'default_selected' => $this->default_selected,
            'price_switch' => $this->price_switch,
//            'varies' => $this->priceVaries(),
            'box' => $this->box->name,
            'box_id' => $this->box->id,
            'parent_id' => $this->parent_id,
            'sku' => $this->sku,
            'last' => (bool)$this->sku,
            'children' => OptionResource::collection($this->option->children),
        ];
    }
}
