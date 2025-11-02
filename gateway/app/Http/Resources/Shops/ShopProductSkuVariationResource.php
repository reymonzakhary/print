<?php

namespace App\Http\Resources\Shops;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use JsonSerializable;

class ShopProductSkuVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource instanceof Collection) {
            return self::collection($this->resource);
        }

        return [
            'id' => $this->id,
            'option_id' => $this->option->row_id,
            'name' => $this->name,
            'sort' => $this->sort,
            'box' => $this->box->name,
            'box_id' => $this->box->id,
            'parent_id' => $this->parent_id,
        ];
    }
}
