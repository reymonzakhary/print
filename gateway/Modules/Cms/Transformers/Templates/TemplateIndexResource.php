<?php

namespace Modules\Cms\Transformers\Templates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Resource\FolderResource;

class TemplateIndexResource extends JsonResource
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
            'sort' => $this->sort,
            'folder' => FolderResource::make($this->folders),
            'type' => $this->type,
            'locked' => $this->locked,
            'static' => $this->static,
        ];
    }
}
