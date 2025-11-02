<?php

namespace App\Http\Resources\DeliveryZone;

use App\Foundation\Settings\Settings;
use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AddressResource
 * @package App\Http\Resources\Address
 * @OA\Schema(
 * )
 */
class DeliveryZoneResource extends JsonResource
{

    protected array $defaultHide = ['created_at', 'updated_at'];

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
        return tap(new DeliveryZoneResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="name", default="Cairo Zone", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="Cairo Zone Containes All Cairo", description="description", property="description"),


     */
    public function toArray($request)
    {
        $convertedPolygon = collect($this->polygon_json)->map(function ($point) {
            return [
                'lat' => (float)$point['lat'],  // Convert lat to float
                'lng' => (float)$point['lng'],  // Convert lng to float
            ];
        });

        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'polygon' => $convertedPolygon,
            'active' => $this->active,
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
