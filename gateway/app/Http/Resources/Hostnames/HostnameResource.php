<?php

namespace App\Http\Resources\Hostnames;

use App\Foundation\ContractManager\Facades\ContractManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HostnameResource extends JsonResource
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
        return tap(new HostnameResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contracts = ContractManager::getMyContracts($this->resource);
        return $this->filterFields(
            $this->resource->getAttribute('custom_fields')->all()
                ->merge([
                    "id" => $this->id,
                    "host_id" => $this->host_id,
                    "fqdn" => $this->fqdn,
                    "logo" => $this->logo_url,
                    "configure" => $this->configure,
                    "primary" => $this->primary,
                    "under_maintenance_since" => $this->under_maintenance_since,
                    "tenant_id" => $this->website->uuid,
                    "supplier" => $this->website->supplier,
                    "external" => $this->website->external,
                    "contracts" => HostnameContractResource::collection($contracts),
                    "address" => $this->company?->addresses->first() ?? null,
                    'manager_language' => $this->custom_fields->pick('manager_language'),
                    'currency' => $this->currency,
//                    'delivery_zones' => DeliveryZoneResource::collection(optional($this->resource)->deliveryZones),
                    'operation_countries' => $this->operationCountries,
                    'external_configure' => $this->website->configure ?? null,
                    "created_at" => $this->created_at,
                    "updated_at" => $this->updated_at,
                    "deleted_at" => $this->deleted_at,
                ])
                ->toArray()
        );
    }

    /**
     *
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
