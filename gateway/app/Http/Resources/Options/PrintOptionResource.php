<?php

namespace App\Http\Resources\Options;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Class PrintOptionResource
 * @package App\Http\Resources\Options\PrintOptionResource
 * @OA\Schema(
 * )
 */
class PrintOptionResource extends JsonResource
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
        return tap(new PrintOptionResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="string", title="id", example="61a8f7824d1302dc3c111df7", description="id", property="id"),
     * @OA\Property(format="string", title="linked", example="61a8f7824d1302dc3c111df7", description="linked", property="linked"),
     * @OA\Property(format="string", title="slug", example="a4", description="slug", property="slug"),
     * @OA\Property(format="string", title="name", example="a4", description="name", property="name"),
     * @OA\Property(format="string", title="display_name", example="a4", description="display_name", property="display_name"),
     * @OA\Property(format="boolean", title="published", example="true", description="published", property="published"),
     * @OA\Property(format="string", title="shareable", example="true", description="shareable", property="shareable"),
     * @OA\Property(format="string", title="sku", example="61a8f7824d1302dc3c111df7", description="sku", property="sku"),
     * @OA\Property(format="string", title="sort", example="2", description="sort", property="sort"),
     * @OA\Property(format="string", title="input_type", example="text", description="input_type", property="input_type"),
     * @OA\Property(format="string", title="media", example="[]", description="media", property="media"),
     * @OA\Property(format="string", title="width", example="0", description="width", property="width"),
     * @OA\Property(format="string", title="height", example="0", description="height", property="height"),
     * @OA\Property(format="string", title="unit", example="mm", description="unit", property="unit"),
     * @OA\Property(format="string", title="maximum", example="0", description="maximum", property="maximum"),
     * @OA\Property(format="string", title="minimum", example="0", description="minimum", property="minimum"),
     * @OA\Property(format="boolean", title="incremental_by", example="True", description="incremental_by", property="incremental_by"),
     * @OA\Property(format="boolean", title="has_children", example="false", description="has_children", property="has_children"),
     * @OA\Property(format="boolean", title="parent", example="false", description="parent", property="parent"),
     * @OA\Property(type="array", title="start_cost", description="start_cost", property="start_cost", @OA\Items(
     *          @OA\Property(format="string", title="value", example="value", description="value", property="value"),
     *          @OA\Property(format="boolean", title="incremental", example="TRUE", description="incremental", property="incremental"),
     *     )),
     * @OA\Property(type="array", title="calculation_method", description="calculation_method", property="calculation_method", @OA\Items(
     *          @OA\Property(format="string", title="type", example="Slide Scale", description="type", property="type"),
     *          @OA\Property(format="boolean", title="active", example="Slide", description="active", property="active"),
     * )),
     * @OA\Property(format="string", title="extended_fields", example="[]", description="extended_fields", property="extended_fields"),
     * @OA\Property(format="string", title="tenant_id", example="123456", description="tenant_id", property="tenant_id"),
     * @OA\Property(format="string", title="tenant_name", example="reseller", description="tenant_name", property="tenant_name"),
     * @OA\Property(format="string", title="description", example="long text here", description="description", property="description"),
     * @OA\Property(format="date", title="created_at", example="1638463338831", description="created_at", property="created_at"),
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        if(!optional($this->resource)['name']) {
            \Log::error(['option without name' => $this->resource]);
        }

        return $this->filterFields(
//            array_merge(optional($this->resource)['configure'] ?? [],
                [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource,'_id')),
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
            "input_type" => optional($this->resource)['input_type'],
            "extended_fields" => optional($this->resource)['extended_fields'],
            "linked" => data_get($this->resource, 'linked.$oid', data_get($this->resource,'linked')),
            "shareable" => optional($this->resource)['shareable'],
            "sku" => optional($this->resource)['sku'],
            "parent" => optional($this->resource)['parent'],
            "rpm" => optional($this->resource)['rpm'],
            "runs" => optional($this->resource)['runs'],
            "sheet_runs" => optional($this->resource)['sheet_runs'],
            "additional" => optional($this->resource)['additional'],
            "boxes" => optional($this->resource)['boxes'],
            "excludes" => optional($this->resource)['excludes'] ?? [],
            // Category Configure fields
            "created_at" => Carbon::createFromTimestampMs(optional($this->resource['created_at'])['$date'])->toDateTimeString(),
            "incremental_by" => optional($this->resource)['incremental_by'],
            "dimension" => optional($this->resource)['dimension'],
            "dynamic" => optional($this->resource)['dynamic'],
            "dynamic_keys" => optional($this->resource)['dynamic_keys'],
            "start_on" => optional($this->resource)['start_on'],
            "end_on" => optional($this->resource)['end_on'],
            "generate" => optional($this->resource)['generate'],
            "dynamic_type" => optional($this->resource)['dynamic_type'],
            "unit" => optional($this->resource)['unit'],
            "width" => optional($this->resource)['width'],
            "maximum_width" => optional($this->resource)['maximum_width'],
            "minimum_width" => optional($this->resource)['minimum_width'],
            "height" => optional($this->resource)['height'],
            "maximum_height" => optional($this->resource)['maximum_height'],
            "minimum_height" => optional($this->resource)['minimum_height'],
            "length" => optional($this->resource)['length'],
            "maximum_length" => optional($this->resource)['maximum_length'],
            "minimum_length" => optional($this->resource)['minimum_length'],
            "start_cost" => optional($this->resource)['start_cost'],
            "calculation_method" => optional($this->resource)['calculation_method'],
        ]
//        )
    );


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
