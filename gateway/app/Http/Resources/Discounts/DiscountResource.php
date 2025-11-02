<?php

namespace App\Http\Resources\Discounts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
        return tap(new DiscountResourceCollection($resource), function ($collection) {
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
        $discount = optional($this)->id ? [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $this->value,
            'display_value' => $this->type === 'percentage' ? "$this->value %" : ((new \App\Plugins\Moneys())->setAmount($this->value))->format(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ] : [];
        return $this->filterFields($discount);
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
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
