<?php

namespace Modules\Cms\Transformers\Snippets\GetResources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Foundation\Traits\HasMedia;

class CategoryResource extends JsonResource
{
    use HasMedia;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->row_id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'iso' => trim($this->iso),
            'sort' => $this->sort,
            'base_id' => $this->base_id,
            'has_children' => (bool)$this->HasChildren(),
            'is_parent' => !$this->parent_id,
            'parent_id' => $this->parent_id,

            'media' => collect($this->media)->map(fn($fm) => $this->getImageUrlFromFileManagerModel($fm))->toArray(),

            'margin_value' => $this->margin_value,
            'margin_type' => $this->margin_type,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,

            'published' => $this->published,
            'published_at' => $this->published_at,
            'published_by' => $this->published_by,

            'products' => ProductIndexResource::collection($this->products)->resolve(),

            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'children' => self::collection($this->whenLoaded('children'))
        ];
    }
}
