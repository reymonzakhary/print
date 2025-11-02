<?php

namespace App\Http\Resources\Country;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class CountryResource
 * @package App\Http\Resources\Country
 * @OA\Schema(
 * )
 */
class CountryResource extends JsonResource
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
        return tap(new CountryResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="int64", title="ID", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="name", default="Egypt", description="name", property="name"),
     * @OA\Property(format="string", title="iso2", default="eg", description="iso2", property="iso2"),
     * @OA\Property(format="string", title="iso3", default="egy", description="iso3", property="iso3"),
     * @OA\Property(format="int64", title="un_code", default="818", description="un_code", property="un_code"),
     * @OA\Property(format="string", title="dial_code", default="002", description="dial_code", property="dial_code"),
     * @OA\Property(format="date", title="created_at", default="2021-09-08T12:19:37.000000Z", description="created date & time", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2021-09-08T12:19:37.000000Z", description="last update date & time", property="updated_at"),
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'iso2' => $this->iso2,
            'iso3' => $this->iso3,
            'un_code' => $this->un_code,
            'dial_code' => $this->dial_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
