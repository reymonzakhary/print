<?php

namespace App\Http\Resources\Cart;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

class CartMediaResource extends JsonResource
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
            "id" => $this->id,
            "user_id" => $this->user_id,
            "model_id" => $this->model_id,
            "model_type" => $this->model_type,
            "name" => $this->name,
            "path" => Str::after($this->path, "{$request->tenant->uuid}/"),
            "group" => $this->group,
            "disk" => $this->disk,
            "ext" => $this->ext,
            "type" => $this->type,
            "showing_columns" => $this->showing_columns,
            "size" => $this->size,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "collection" => $this->collection,
            "external" => $this->external
        ];
    }
}
