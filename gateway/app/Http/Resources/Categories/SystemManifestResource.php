<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemManifestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, 'id.$oid', data_get($this->resource, '_id'))),
            "slug" => data_get($this->resource, 'slug'),
            "ref_id" => data_get($this->resource, 'ref_id'),
            "ref_boops_name" => data_get($this->resource, 'ref_boops_name'),
            "name" => data_get($this->resource, 'name'),
            "display_name" => data_get($this->resource, 'display_name'),
            "system_key" => data_get($this->resource, 'system_key'),
            "published" => data_get($this->resource, 'published'),
            "shareable" => data_get($this->resource, 'shareable'),
            "generated" => data_get($this->resource, 'generated'),
            "divided" => data_get($this->resource, 'divided'),
            "shared" => data_get($this->resource, 'shared'),
            "has_manifest" => data_get($this->resource, 'has_manifest'),
            "category" => data_get($this->resource, 'category.$oid', data_get($this->resource, 'category')),
            "boops" => $this->boxes(data_get($this->resource, 'boops', [])),
        ];
    }

    public function boxes(array $boxes)
    {
        return collect($boxes)->map(fn($box) => [
            'id' => data_get($box, '_id.$oid', data_get($box, 'id.$oid', data_get($box,'id'))),
            'sort' => data_get($box, 'sort'),
            'name' => data_get($box, 'name'),
            'sku' => data_get($box, 'sku'),
            'system_key' => data_get($box, 'system_key'),
            'display_name' => data_get($box, 'display_name', data_get($box, 'name')),
            'slug' => data_get($box, 'slug'),
            'description' => data_get($box, 'description'),
            'media' => data_get($box, 'media'),
            'divider' => data_get($box, 'divider'),
            'sqm' => data_get($box, 'sqm'),
            'incremental' => data_get($box, 'incremental'),
            'published' => data_get($box, 'published'),
            'select_limit' => data_get($box, 'select_limit'),
            'option_limit' => data_get($box, 'option_limit'),
            'input_type' => data_get($box, 'input_type'),
            'additional' => data_get($box, 'additional'),
            'ops' => $this->options(data_get($box, 'ops', []))
        ]);

    }

    public function options($options)
    {
        return collect($options)->map(function ($option) {
            if (is_array($option)) {
                return [
                    'id' => data_get($option, '_id.$oid', data_get($option, 'id.$oid', data_get($option,'id'))),
                    'sort' => data_get($option, 'sort'),
                    'name' => data_get($option, 'name'),
                    'display_name' => data_get($option, 'display_name', data_get($option, 'name')),
                    'slug' => data_get($option, 'slug'),
                    'sku' => data_get($option, 'sku'),
                    'system_key' => data_get($option, 'system_key'),
                    'description' => data_get($option, 'description'),
                    'information' => data_get($option, 'information'),
                    'media' => data_get($option, 'media', []),
                    'incremental_by' => data_get($option, 'incremental_by'),

                    'dimension' => data_get($option, 'dimension'),
                    'dynamic' => data_get($option, 'dynamic'),
                    'unit' => data_get($option, 'unit'),
                    'width' => data_get($option, 'width'),
                    'maximum_width' => data_get($option, 'maximum_width'),
                    'minimum_width' => data_get($option, 'minimum_width'),

                    'height' => data_get($option, 'height'),
                    'maximum_height' => data_get($option, 'maximum_height'),
                    'minimum_height' => data_get($option, 'minimum_height'),

                    'length' => data_get($option, 'length'),
                    'maximum_length' => data_get($option, 'maximum_length'),
                    'minimum_length' => data_get($option, 'minimum_length'),

                    'parent' => data_get($option, 'parent'),
                    'published' => data_get($option, 'published'),
                    'has_children' => data_get($option, 'has_children'),
                    'input_type' => data_get($option, 'input_type'),
                    'extended_fields' => data_get($option, 'extended_fields', []),
                ];
            }
        });

    }
}
