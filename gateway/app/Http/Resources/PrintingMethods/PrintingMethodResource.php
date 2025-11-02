<?php

namespace App\Http\Resources\PrintingMethods;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class PrintingMethodResource
 * @package App\Http\Resources\PrintingMethods
 * @OA\Schema(
 * )
 */
class PrintingMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    /**
     * @OA\Property(format="int64", title="ID", example=1, description="ID", property="id"),
     * @OA\Property(format="string", title="name", example="offset", description="name", property="name"),
     * @OA\Property(format="string", title="slug", example="offset", description="slug", property="slug"),
     * @OA\Property(format="string", title="iso", example="en", description="iso", property="iso"),
     * @OA\Property(format="int64", title="sort", example="1", description="sort", property="sort"),
     * @OA\Property(format="int64", title="row_id", example="1", description="row_id", property="row_id")
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'iso' => $this->iso,
            'sort' => $this->sort,
            'row_id' => $this->row_id,
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
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
