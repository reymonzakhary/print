<?php

namespace Modules\Cms\Transformers\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChunkResource extends JsonResource
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
            'path' => $this->path,
            'folder' => FolderResource::make($this->folders),
            'content' => $this->content,
            'sort' => $this->sort,
            'short_code' => "[[\${$this->name}]]"
        ];
    }
}
