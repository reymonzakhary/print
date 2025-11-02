<?php

namespace App\Http\Resources\Address;

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
class AddressResource extends JsonResource
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
        return tap(new AddressResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="address", default="kilany street", description="address", property="address"),
     * @OA\Property(format="string", title="number", default="9", description="number", property="number"),
     * @OA\Property(format="string", title="city", default="cairo", description="city", property="city"),
     * @OA\Property(format="string", title="region", default="naser city", description="region", property="region"),
     * @OA\Property(format="int64", title="zip_code", default="119911", description="zip_code", property="zip_code"),
     * @OA\Property(title="country", ref="#/components/schemas/CountryResource", description="country details", property="country"),
     * @OA\Property(format="string", title="type", default="Work", description="type", property="type"),
     * @OA\Property(format="string", title="full_name", default="Hifnico", description="full_name", property="full_name"),
     * @OA\Property(format="string", title="company_name", default="Cairo house developments", description="company_name", property="company_name"),
     * @OA\Property(format="string", title="phone_number", default="01125902552", description="phone_number", property="phone_number"),
     * @OA\Property(format="int64", title="tax_nr", default="123456", description="tax_nr", property="tax_nr"),
     * @OA\Property(format="string", title="lat", default="21.3540", description="lat", property="lat"),
     * @OA\Property(format="string", title="lng", default="22.3026", description="lng", property="lng"),
     * @OA\Property(format="date", title="created_at", default="2021-09-08T12:19:37.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2021-09-08T12:19:37.000000Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        $team_address = $this->pivot?->team_address;
        $team_id = $this->pivot?->pivotParent?->id??$this->pivot?->team_id;
        $team_name = $this->pivot?->pivotParent?->name??$this->pivot?->team_name;

        return $this->filterFields([
            'id' => $this->id,
            'team_address' => $team_address,
            'team_id' => $team_address?$team_id:null,
            'team_name' => $team_name,
            'address' => $this->address,
            'number' => $this->number,
            'city' => $this->city,
            'region' => $this->region,
            'zip_code' => $this->zip_code,
            'default' => $this->pivot->default ?? false,
            'country' => CountryResource::make($this->whenLoaded('country'))->hide($this->defaultHide),
            'type' => $this->pivot->type ?? null,
            'full_name' => $this->pivot->full_name ?? null,
            'company_name' => $this->pivot->company_name ?? null,
            'dial_code' => $this->pivot->dial_code ?? null,
            'phone_number' => $this->pivot->phone_number ?? null,
            'tax_nr' => $this->pivot->tax_nr ?? null,
            'lat' => $this->lat,
            'lng' => $this->lng,
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
