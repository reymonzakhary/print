<?php

namespace App\Http\Resources\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return optional($this->resource)['row_id']?
            parent::toArray($request): [
            "_" => optional($this->resource)['_'] ?? [],
            "key" => $this->resource['key'],
            "value" => $this->resource['value'],
            "divider" => optional($this->resource)['divider']??"",
            "dynamic" => optional($this->resource)['dynamic']?filter_var($this->resource['dynamic'], FILTER_VALIDATE_BOOLEAN):false
        ];
    }
}
