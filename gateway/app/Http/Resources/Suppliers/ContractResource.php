<?php

namespace App\Http\Resources\Suppliers;

use App\Http\Resources\Statuses\StatusResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    protected array $defaultHide = ['created_at'];
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
        return tap(new ContractResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'st' => StatusResource::make($this->st),
            'activated_at' => $this->activated_at,
            'active' => $this->active,
            'callback' => $this->callback,
            'created_at' => $this->created_at,
            'supplier' => HostNameResource::make($this->supplier)->hide(['uuid', 'config', 'host_id']),
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
