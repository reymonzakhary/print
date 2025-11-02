<?php

namespace App\Http\Resources\Categories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class AclCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->row_id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'iso' => trim($this->iso),
            'children' => self::collection($this->whenLoaded('children'))
        ];
    }
}
