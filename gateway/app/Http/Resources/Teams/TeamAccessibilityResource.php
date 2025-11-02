<?php

namespace App\Http\Resources\Teams;

use App\Http\Resources\Categories\CategoryResource;
use App\Http\Resources\Teams\PrintCategoryResource;
use App\Http\Resources\Products\ProductMainResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamAccessibilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'categories' => CategoryResource::collection($this->category)->hide([
                'sort',
                'base_id' => $this?->base_id,
                'has_children',
                'is_parent',
                'parent_id',
                'media',
                'margin_value',
                'margin_type',
                'discount_value',
                'discount_type',
                'published',
                'published_at',
                'published_by',
                'created_by',
                'created_at',
                'updated_at'
            ]),
            'print_categories' => PrintCategoryResource::collection($this->print_categories),
            'products' => ProductMainResource::collection($this->product)
        ];
    }
}
