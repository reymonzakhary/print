<?php

namespace App\Http\Resources\Shops;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShopVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "single" => $this->single,
            "upto" => $this->upto,
            "mime_type" => $this->mime_type,
            "parent_id" => $this->parent_id,
            "incremental_by" => $this->incremental_by,
            "switch_price" => $this->switch_price,
            "expire_date" => $this->expire_date,
            "appendage" => $this->appendage,
            "child" => $this->child,
            "expire_after" => $this->expire_after,
            "description" => optional($this->option)->description,
            "input_type" => optional($this->option)->input_type,

            'id' => $this->id,
            'option_id' => $this->option_id,
            "name" => $this->name,
            "sort" => $this->sort,
            "price" => $this->price->amount(),
            "display_price" => $this->price->format(),
            "sale_price" => $this->sale_price,
            "incremental" => $this->incremental,
            "published" => $this->published,
            "override" => $this->override,
            "free" => $this->free,
            "default_selected" => $this->default_selected,
            "price_switch" => $this->price_switch,
            "media" => $this->media,
            "properties" => $this->properties ?? $this->option->properties,
        ];
    }
}
