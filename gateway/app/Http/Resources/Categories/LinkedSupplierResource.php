<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkedSupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id', $this->resource['id'] ?? null)),
            "sort" => data_get($this->resource, 'sort'),
            "slug" => data_get($this->resource, 'slug'),
            "tenant_id" => data_get($this->resource, 'tenant_id'),
            "tenant_name" => data_get($this->resource, 'tenant_name'),
            "countries" => data_get($this->resource, 'countries'),
            "sku" => data_get($this->resource, 'sku'),
            "name" => data_get($this->resource, 'name'),
            "system_key" => data_get($this->resource, 'system_key'),
            "linked" => data_get($this->resource, 'linked.$oid'),
        ];
    }
}
