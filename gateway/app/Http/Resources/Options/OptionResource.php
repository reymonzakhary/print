<?php


namespace App\Http\Resources\Options;


use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OptionResource
 * @package App\Http\Resources\OptionResource
 * @OA\Schema(
 *     schema="OptionResource",
 *     title="Option Resource"
 *
 * )
 */
class OptionResource extends JsonResource
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
        return tap(new OptionResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="red", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="red", description="description", property="description"),
     * @OA\Property(format="string", title="slug", default="Red", description="slug", property="slug"),
     * @OA\Property(format="string", title="box_id", default=1, description="box_id", property="box_id"),
     * @OA\Property(format="string", title="input_type", default="checkbox", description="input_type", property="input_type"),
     * @OA\Property(format="string", title="incremental_by", default=1, description="incremental_by", property="incremental_by"),
     * @OA\Property(format="string", title="min", default=1, description="min", property="min"),
     * @OA\Property(format="string", title="max", default=12, description="max", property="max"),
     * @OA\Property(format="string", title="width", default=20, description="width", property="width"),
     * @OA\Property(format="string", title="height", default=20, description="height", property="height"),
     * @OA\Property(format="string", title="length", default=20, description="length", property="length"),
     * @OA\Property(format="string", title="unit", default=20, description="unit", property="unit"),
     * @OA\Property(format="string", title="display_price", default=20, description="display_price", property="display_price"),
     * @OA\Property(format="string", title="price", default=20, description="price", property="price"),
     * @OA\Property(format="string", title="sale_price", default=50, description="sale_price", property="sale_price"),
     * @OA\Property(format="string", title="price_switch", default=true, description="price_switch", property="price_switch"),
     * @OA\Property(format="string", title="sort", default=true, description="sort", property="sort"),
     * @OA\Property(format="string", title="secure", default=false, description="secure", property="secure"),
     * @OA\Property(format="string", title="parent_id", default=1, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="iso", default=2, description="iso", property="iso"),
     * @OA\Property(format="string", title="base_id", default=2, description="base_id", property="base_id"),
     * @OA\Property(format="string", title="row_id", default=2, description="row_id", property="row_id"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=2, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="today", description="published_at", property="published_at"),
     * @OA\Property(type="array", property="children", @OA\Items(ref="#/components/schemas/OptionResource"))
     * @OA\Property(format="string", title="created_at", default="en", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="en", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->row_id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'box_id' => $this->box_id,
            'input_type' => $this->input_type,
            'incremental_by' => $this->incremental_by,
            'min' => $this->min,
            'max' => $this->max,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'unit' => $this->unit,
            'single' => $this->single,
            'upto' => $this->upto,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'price_switch' => $this->price_switch,
            'sort' => $this->sort,
            'secure' => $this->secure,
            'parent_id' => $this->parent_id,
            'iso' => $this->iso,
            'base_id' => $this->base_id,
            'published' => $this->published,
            'created_by' => $this->created_by,
            'published_by' => $this->published_by,
            'published_at' => $this->published_at,
            'children' => self::collection($this->children),
            'properties' => $this->properties,
            'media' => collect($this->media)->map(fn($md) => $md->path . $md->name)->toArray(),
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
