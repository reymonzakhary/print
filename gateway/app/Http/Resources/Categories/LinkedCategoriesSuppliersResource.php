<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkedCategoriesSuppliersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "linked" => data_get($this->resource, 'linked'),
            "suppliers" => $this->suppliers(data_get($this->resource, 'data', [])),
        ];
    }

    public function suppliers(array $suppliers)
    {
        return collect($suppliers)->map(fn($supplier) => [
            'id' => data_get($supplier, 'id'),
            'tenant_name' => data_get($supplier, 'tenant_name'),
            'name' => data_get($supplier, 'name'),
        ]);

    }

}
