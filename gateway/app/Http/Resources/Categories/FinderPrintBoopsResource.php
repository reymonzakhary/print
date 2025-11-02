<?php

namespace App\Http\Resources\Categories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class FinderPrintBoopsResource extends JsonResource
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
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, 'id')),
            "slug" => $this->resource['slug'],
            "name" => $this->resource['name'],
            "display_name" => optional($this->resource)['display_name'],
            "published" => optional($this->resource)['published'],
            "shareable" => optional($this->resource)['shareable'],
            "generated" => optional($this->resource)['generated'],
            "checked" => optional($this->resource)['checked'],
            "sort" => optional($this->resource)['sort'],
            "iso" => optional($this->resource)['iso'],
            "description" => optional($this->resource)['description'],
            "boops" => $this->boxes(optional($this->resource)['boxes']),
            "media" => optional($this->resource)['media'],
            "linked" => optional(collect(PrintCategoryResource::collection(collect(['data' => optional($this->resource)['linked']]))))['data'],
            "created_at" => optional(optional($this->resource)['created_at'])['$date']
        ];

    }

    public function boxes(array $boxes)
    {
        return collect($boxes)->map(fn($box) => [
            'id' => $this->id($box),
            'sort' => $box['sort'],
            'name' => optional($box)['name'],
            'sku' => optional($box)['sku'],
            'system_key' => optional($box)['system_key'],
            'display_name' => optional($box)['display_name'] ?? $box['name'],
            'slug' => $box['slug'],
            'description' => $box['description'],
            'media' => $box['media'],
            'sqm' => $box['sqm'],
            'incremental' => $box['incremental'],
            'published' => $box['published'],
            'select_limit' => $box['select_limit'],
            'option_limit' => $box['option_limit'],
            'input_type' => $box['input_type'],
            'additional' => $box['additional'],
            'ops' => $this->options($box['options'])
        ]);

    }

    public function options($options)
    {
        return collect($options)->map(function ($option) {
            if (is_array($option)) {
                return [
                    'id' => $this->id($option),
                    'sort' => $option['sort'],
                    'name' => $option['name'],
                    'display_name' => optional($option)['display_name'] ?? $option['name'],
                    'slug' => optional($option)['slug'],
                    'sku' => optional($option)['sku'],
                    'system_key' => optional($option)['system_key'],
                    'description' => optional($option)['description'],
                    'information' => optional($option)['information'],
                    'media' => $option['media'],
                    'incremental_by' => $option['incremental_by'],

                    'dimension' => optional($option)['dimension'],
                    'dynamic' => optional($option)['dynamic'],
                    'unit' => $option['unit'],
                    'width' => $option['width'],
                    'maximum_width' => $option['maximum_width'],
                    'minimum_width' => $option['minimum_width'],

                    'height' => $option['height'],
                    'maximum_height' => $option['maximum_height'],
                    'minimum_height' => $option['minimum_height'],


                    'length' => $option['length'],
                    'maximum_length' => $option['maximum_length'],
                    'minimum_length' => $option['minimum_length'],

                    'parent' => $option['parent'],
                    'published' => $option['published'],
                    'has_children' => $option['has_children'],
                    'input_type' => $option['input_type'],
                    'extended_fields' => $option['extended_fields'],

                ];
            }

        });

    }

    protected function id($obj)
    {
        if (is_array($obj)) {
            return optional($obj['_id'])['$oid'] ?? $obj['_id'];
        }
    }
}
