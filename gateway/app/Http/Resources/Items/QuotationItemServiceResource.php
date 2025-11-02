<?php

namespace App\Http\Resources\Items;

use App\Http\Resources\Media\MediaResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemServiceResource extends JsonResource
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
        return tap(new QuotationItemServiceResourceCollection($resource), function ($collection) {
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'display_price' => $this->formattedPrice,
            'price' => $this->price->amount(),
            'vat_id' => $this->vat_id,
            'attachments' => MediaResource::collection($this->getMedia('quotation-item-services')),
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
