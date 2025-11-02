<?php

namespace App\Http\Resources\Media;

use App\Http\Resources\Tags\TagResource;
use App\Models\Traits\InteractsWithMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    use InteractsWithMedia;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'disk' => $this->disk,
            'size' => $this->size,
            'extension' => $this->ext,
            'url' => $this->getPublicFileUrl($this->disk, $this->path, $this->name),
            'path' => $this->getImagePath($this->path, $this->name),
            'tags' => TagResource::collection($this->tags??[]),
            'created_at' => $this->create_at,
            'updated_at' => $this->updated_at
        ];

    }
}
