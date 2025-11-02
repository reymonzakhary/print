<?php

namespace Modules\Cms\Transformers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceTypeResource extends JsonResource
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
            "name" => $this->name,
			"slug" => $this->slug,
			"mime_type" => $this->mime_type,
			"file_extensions" => $this->file_extensions,
			"headers" => $this->headers,
			"binary" => $this->binary,
        ];

    }
}
