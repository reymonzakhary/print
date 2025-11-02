<?php

namespace App\Http\Resources\Options;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnmatchedOptionResource extends JsonResource
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
            "sort" => data_get($this->resource, 'sort', 0),
            "name" => data_get($this->resource, 'name'),
            "slug" => data_get($this->resource, 'slug'),
            "tenant_id" => data_get($this->resource, 'tenant_id'),
            "tenant_name" => data_get($this->resource, 'tenant_name'),
            "description" => data_get($this->resource, 'description', ''),
            "media" => data_get($this->resource, 'media', ''),
            "sku" => data_get($this->resource, 'sku', ''),
            "published" => data_get($this->resource, 'published', true),
            "created_at" => Carbon::createFromTimestamp(
                data_get($this->resource, 'created_at.$date', 0) / 1000, 'UTC'
            )->toDateTimeString(),
        ];
    }
}
