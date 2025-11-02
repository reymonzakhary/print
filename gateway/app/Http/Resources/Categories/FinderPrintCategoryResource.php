<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Resources\Json\JsonResource;

class FinderPrintCategoryResource extends JsonResource
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
        return tap(new FinderPrintCategoryResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            "id" => $this->id($this->resource),
            "slug" => $this->resource['slug'],
            "name" => $this->resource['name'],
            "display_name" =>  optional($this->resource)['display_name'],
            "published" => optional($this->resource)['published'],
            "checked" => optional($this->resource)['checked'],
            "sort" => optional($this->resource)['sort'],
            "iso" => optional($this->resource)['iso'],
            "description" => optional($this->resource)['description'],
            "media" => optional($this->resource)['media'],
            "linked" => optional(collect(PrintCategoryResource::collection(collect(optional($this->resource)['linked'])))),
            "created_at" => optional(optional($this->resource)['created_at'])['$date']
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

    protected function id($obj)
    {
        return optional($obj['_id'])['$oid'] ?? $obj['_id'];
    }
}
