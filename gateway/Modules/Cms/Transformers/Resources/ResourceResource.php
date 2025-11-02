<?php

namespace Modules\Cms\Transformers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        $content = [];
        if ($this->content && is_array($this->content)) {
            foreach ($this->content as $key => $object) {
                $content[$key] = $object;
                if (optional($object)['type'] === 'file') {
                    $content[$key]['url'] = optional($this->getMedia(optional($object)['key']))[optional($object)['key']];
                }
            }
        }
        return [
            'id' => $this->resource_id,
            'title' => $this->title,
            'long_title' => $this->long_title,
            'intro_text' => $this->intro_text,
            'description' => $this->description,
            'menu_title' => $this->menu_title,
            'resource_type' => ResourceTypeResource::make($this->resourceType),
            'uri' => $this->uri,
            'slug' => $this->slug,
            'language' => $this->language,
            'content' => $content,
            'sort' => $this->sort,
            'isfolder' => !((bool) $this->parent_id),
            'menu_index' => $this->menu_index,
            'published' => $this->published,
            'hidden' => $this->hidden,
            'searchable' => $this->searchable,
            'cacheable' => $this->cacheable,
            'hide_children_in_tree' => $this->hide_children_in_tree,
            'created_by' => $this->createdby,
            'updated_by' => $this->updatedby,
            'deleted_by' => $this->deletedby,
            'published_by' => $this->publishedby,
            'locked_by' => $this->lockedby,
            'template' => optional($this->template)->id,
            'variables' => optional($this->template)->variables,
            'ctx_id' => $this->ctx_id,
            'parent_id' => $this->parent_id,
            'resource_type_id' => $this->resource_type_id,
            'published_on' => $this->published_on,
            'image' => collect($this->getMedia('main', 'path', 'assets'))->first(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];

    }
}
