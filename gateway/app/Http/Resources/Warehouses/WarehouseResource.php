<?php

namespace App\Http\Resources\Warehouses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class WarehouseResource
 * @package App\Http\Resources\WarehouseResource
 * @OA\Schema(
 * )
 */
class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     *
     * @OA\Property(format="int64", title="id",example="1", description="id", property="id"),
     * @OA\Property(format="string", title="name",example="warehouses 1", description="name", property="name"),
     * @OA\Property(format="string", title="slug",example="warehouses-1", description="slug", property="slug"),
     * @OA\Property(format="int64", nullable=true, title="sort",example="1", description="sort", property="sort"),
     * @OA\Property(format="string", title="description",example="description", description="description", property="description"),
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort' => $this->sort,
            'description' => $this->description,
        ];
    }
}
