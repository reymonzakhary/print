<?php

declare(strict_types=1);

namespace App\Http\Resources\Boxes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PrintBoxResource
 * @package App\Http\Resources\Boxes\PrintBoxResource
 * @OA\Schema(
 * )
 */
final class PrintBoxResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection(
        mixed $resource
    ): mixed
    {
        return tap(new PrintBoxResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="string", title="ID", example="61a8f7824d1302dc3c111df7", description="ID", property="id"),
     * @OA\Property(format="string", title="linked", example="61a8f7824d1302dc3c111df7", description="linked", property="linked"),
     * @OA\Property(format="string", title="slug", example="format", description="slug", property="slug"),
     * @OA\Property(format="string", title="name", example="format", description="name", property="name"),
     * @OA\Property(format="string", title="display_name", example="format", description="display_name", property="display_name"),
     * @OA\Property(format="boolean", title="published", example="true", description="published", property="published"),
     * @OA\Property(format="boolean", title="shareable", example="true", description="shareable", property="shareable"),
     * @OA\Property(format="string", title="select_limit", example="5", description="select_limit", property="select_limit"),
     * @OA\Property(format="string", title="option_limit", example="5", description="option_limit", property="option_limit"),
     * @OA\Property(format="string", title="input_type", example="Text", description="input_type", property="input_type"),
     * @OA\Property(format="boolean", title="incremental", example="true", description="incremental", property="incremental"),
     * @OA\Property(format="boolean", title="sqm", example="false", description="square meter product", property="sqm"),
     * @OA\Property(format="boolean", title="media", example="[]", description="media", property="media"),
     * @OA\Property(format="boolean", title="sku", example="true", description="sku", property="sku"),
     * @OA\Property(format="string", title="sort", example="2", description="sort", property="sort"),
     * @OA\Property(format="string", title="tenant_id", example="123456", description="tenant_id", property="tenant_id"),
     * @OA\Property(format="string", title="tenant_name", example="reseller", description="tenant_name", property="tenant_name"),
     * @OA\Property(format="string", title="description", example="long text here", description="description", property="description"),
     * @OA\Property(format="date", title="created_at", example="1638463338831", description="created_at", property="created_at"),
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(
        Request $request
    ): array
    {

        return $this->filterFields([
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id', $this->resource['id'] ?? null)),
            "linked" => data_get($this->resource,'linked.$oid',optional($this->resource)['linked']),
            "slug" => optional($this->resource)['slug'],
            "name" => optional($this->resource)['name'],
            "display_name" => optional($this->resource)['display_name'],
            "system_key" => optional($this->resource)['system_key'],
            "published" => optional($this->resource)['published'],
            "appendage" => optional($this->resource)['appendage'],
            "shareable" => optional($this->resource)['shareable'],
            "select_limit" => optional($this->resource)['select_limit'],
            "option_limit" => optional($this->resource)['option_limit'],
            "input_type" => optional($this->resource)['input_type'],
            "ops" => $this->resource['ops'] ?? [],
            "calc_ref" => optional($this->resource)['calc_ref'],
            "incremental" => optional($this->resource)['incremental'],
            "sqm" => optional($this->resource)['sqm'],
            "media" => optional($this->resource)['media'],
            "sku" => optional($this->resource)['sku'],
            "sort" => optional($this->resource)['sort'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "description" => optional($this->resource)['description'],
            "additional" => optional($this->resource)['additional'],
            "matched" => $this->when(optional($this->resource)['matched'],optional(optional($this->resource)['matched']), null),
            "created_at" => data_get(
                $this->resource,
                'created_at.$date',   
                Carbon::parse(optional($this->resource)['created_at'] , 'UTC')->toDateTimeString()
                )
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hide(
        array $fields
    ): static
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     *
     * @return array
     */
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }

}
