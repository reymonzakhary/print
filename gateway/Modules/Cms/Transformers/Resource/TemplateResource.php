<?php

namespace Modules\Cms\Transformers\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'description' => $this->description,
            'folder' => FolderResource::make($this->folders),
            'type' => $this->type,
            'content' => $this->content,
            'locked' => $this->locked,
            'properties' => $this->properties,
            'static' => $this->static,
            'path' => $this->path,
            'variables' => TemplateVariableResource::collection($this->variables),
            'chunks' => ChunkResource::collection($this->chunks),
        ];
    }
}
