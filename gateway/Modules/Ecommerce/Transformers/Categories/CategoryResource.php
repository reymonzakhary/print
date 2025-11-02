<?php

namespace Modules\Ecommerce\Transformers\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'iso' => $this->iso,
            'depth' => $this->depth,
            'path' => $this->path,
            'slug_path' => $this->slug_path,
            'has_children' => (bool)$this->children->count(),
            'is_parent' => !$this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'children' => self::collection($this->whenLoaded('children'))
        ];
    }
}
