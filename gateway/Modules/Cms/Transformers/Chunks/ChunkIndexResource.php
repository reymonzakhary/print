<?php

namespace Modules\Cms\Transformers\Chunks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Resource\FolderResource;

class ChunkIndexResource extends JsonResource
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
            'sort' => $this->sort,
            'folder' => FolderResource::make($this->folders),
            'short_code' => "[[\${$this->name}]]"
        ];
    }
}
