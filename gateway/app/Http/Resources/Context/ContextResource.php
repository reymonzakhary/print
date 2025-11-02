<?php

namespace App\Http\Resources\Context;

use App\Http\Resources\Address\AddressResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ContextResource
 * @package App\Http\Resources\Context
 * @OA\Schema(
 * )
 */
class ContextResource extends JsonResource
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
        return tap(new ContextResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */

    /**
     * @OA\Property(format="int64", title="ID", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="name", default="admin", description="role name", property="name"),
     * @OA\Property(format="string", title="description", default="nither", description="description", property="description"),
     * @OA\Property(format="string", title="config", default="xingxiang", description="config", property="config"),
     * @OA\Property(type="array", property="addresses", @OA\Items(ref="#/components/schemas/AddressResource")),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'config' => $this->config,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses'))->hide([]),
            'member' => optional($this->pivot)->member,

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
