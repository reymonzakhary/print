<?php

namespace App\Http\Resources\Media;

use App\Http\Resources\Tags\TagResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

class FilemanagerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "path" => $this->path . DIRECTORY_SEPARATOR . $this->name,
            "dirname" => $this->path,
            "basename" => $this->name,
            "extension" => $this->ext,
            "disk" => $this->disk,
            "filename" => Str::before($this->name, '.'),
            "timestamp" => $this->created_at->timestamp,
            "size" => $this->size,
            "storageclass" => "STANDARD",
//			"etag"=> "\"7ee2f68a74d6fffc4a725b5a30a5b979\"",
            "type" => "file",
            "tags" => TagResource::collection($this->tags)
        ];
    }
}
