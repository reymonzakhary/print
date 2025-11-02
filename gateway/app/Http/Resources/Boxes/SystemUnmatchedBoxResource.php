<?php

namespace App\Http\Resources\Boxes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemUnmatchedBoxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id', $this->resource['id'] ?? null)),
            'sort' => data_get($this->resource, 'sort'),
            'tenant_id' => data_get($this->resource, 'tenant_id'),
            'tenant_name' => data_get($this->resource, 'tenant_name'),
            'name' => data_get($this->resource, 'name'),
            'slug' => data_get($this->resource, 'slug'),
            'sku' => data_get($this->resource, 'sku'),
            'description' => data_get($this->resource, 'description'),
            'media' => data_get($this->resource, 'media'),
            'published' => data_get($this->resource, 'published'),
            'created_at' => data_get($this->resource, 'created_at.$date'),
        ];
    }
}
