<?php

namespace App\Http\Resources\Options;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintOptionIndexResource extends JsonResource
{

    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new PrintOptionIndexResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id')),
            "sort" => optional($this->resource)['sort'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "name" => $this->resource['name'],
            "display_name" => optional($this->resource)['display_name'],
            "slug" => $this->resource['slug'],
            "system_key" => optional($this->resource)['system_key'],
            "description" => optional($this->resource)['description'],
            "media" => optional($this->resource)['media'],
            "published" => $this->resource['published'],
            "has_children" => optional($this->resource)['has_children'],
            "linked" => data_get($this->resource, 'linked.$oid', data_get($this->resource, 'linked')),
            "input_type" => optional($this->resource)['input_type'],
            "extended_fields" => optional($this->resource)['extended_fields'],
            "shareable" => optional($this->resource)['shareable'],
            "sku" => optional($this->resource)['sku'],
            "parent" => optional($this->resource)['parent'],
            "rpm" => optional($this->resource)['rpm'],
            "additional" => optional($this->resource)['additional'],
            "created_at" => Carbon::createFromTimestampMs(optional($this->resource['created_at'])['$date'])->toDateTimeString()
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
