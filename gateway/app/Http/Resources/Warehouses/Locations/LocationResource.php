<?php

namespace App\Http\Resources\Warehouses\Locations;

use App\Http\Resources\Warehouses\WarehouseResource;
use App\Models\Tenants\Warehouse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class LocationResource
 * @package App\Http\Resources\LocationResource
 * @OA\Schema(
 * )
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="int64", title="warehouse_id", default=1, description="warehouse_id", property="warehouse_id"),
     * @OA\Property(format="int64", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="string", title="ean", default="0123456789012", description="ean", property="ean"),
     * @OA\Property(format="string", title="position", default="asdas-asdasd-ada", description="position", property="position"),
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'warehouse' => WarehouseResource::make(Warehouse::find($this->warehouse_id)),
            'sort' => $this->sort,
            'ean' => $this->ean,
            'position' => $this->position,
        ];
    }
}
