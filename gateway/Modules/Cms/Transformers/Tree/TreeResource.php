<?php

namespace Modules\Cms\Transformers\Tree;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreeResource extends JsonResource
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
            'id' => $this->resource_id,
            'title' => $this->title,
            'parent_id' => $this->parent_id,
            'language' => $this->language,
            'sort' => $this->sort,
            'isfolder' => $this->isfolder,
            'published' => $this->published,
            'hidden' => $this->hidden,
            'hide_children_in_tree' => $this->hide_children_in_tree,
        ];
    }
}
