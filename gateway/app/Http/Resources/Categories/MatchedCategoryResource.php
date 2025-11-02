<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchedCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this, '_id.$oid'),
            'sort' => data_get($this, 'sort', 0),
            'tenant_id' => data_get($this, 'tenant_id'),
            'tenant_name' => data_get($this, 'tenant_name'),
            'name' => data_get($this, 'name'),
            'slug' => data_get($this, 'slug'),
            'sku' => data_get($this, 'sku'),
            'description' => data_get($this, 'description'),
            'media' => data_get($this, 'media', []), // Default to empty array if media is missing
            'percentage' => data_get($this, 'percentage', 0),
            'published' => data_get($this, 'published', false),
            'category' => self::collection(data_get($this, 'category')??[]),
        ];
    }
}
