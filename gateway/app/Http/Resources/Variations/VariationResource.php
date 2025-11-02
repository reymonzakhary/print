<?php


namespace App\Http\Resources\Variations;


use App\Http\Resources\Boxes\BoxResource;
use App\Http\Resources\Categories\CategoryResource;
use App\Http\Resources\Options\OptionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationResource extends JsonResource
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
        return tap(new VariationResourceCollection($resource), function ($collection) {
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
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'box' => BoxResource::make($this->whenLoaded('box')),
            'option' => OptionResource::make($this->whenLoaded('option')),
            'price' => $this->price,
            'incremental' => $this->incremental,
            'sort' => $this->sort,
            'published' => $this->published,
            'override' => $this->override,
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
