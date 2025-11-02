<?php


namespace App\Http\Resources\Boxes;


use App\Http\Resources\Options\OptionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CustomBoxResource
 * @package App\Http\Resources\BoxResource
 * @OA\Schema(
 *     schema="CustomBoxResource",
 *     title="Box Resource"
 *
 * )
 */
class BoxResource extends JsonResource
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
        return tap(new BoxResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="color", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="color", description="description", property="description"),
     * @OA\Property(format="string", title="slug", default="Color", description="slug", property="slug"),
     * @OA\Property(format="string", title="input_type", default="checkbox", description="input_type", property="input_type"),
     * @OA\Property(format="string", title="incremental", default=true, description="incremental", property="incremental"),
     * @OA\Property(format="string", title="select_limit", default=1, description="select_limit", property="select_limit"),
     * @OA\Property(format="string", title="option_limit", default=2, description="option_limit", property="option_limit"),
     * @OA\Property(format="string", title="sqm", default=true, description="sqm", property="sqm"),
     * @OA\Property(format="string", title="iso", default=1, description="iso", property="iso"),
     * @OA\Property(format="string", title="base_id", default=2, description="base_id", property="base_id"),
     * @OA\Property(format="string", title="is_parent", default=false, description="is_parent", property="is_parent"),
     * @OA\Property(format="string", title="media", default="image", description="media", property="media"),
     * @OA\Property(format="string", title="options", default="red", description="options", property="options"),
     * @OA\Property(format="string", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="created_at", default="today", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="today", description="updated_at", property="updated_at"),
     * @OA\Property(type="array", property="children", @OA\Items(ref="#/components/schemas/CustomBoxResource"))
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->row_id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'input_type' => $this->input_type,
            'incremental' => $this->incremental,
            'select_limit' => $this->select_limit,
            'option_limit' => $this->option_limit,
            'sqm' => $this->sqm,
            'iso' => trim($this->iso),
            'base_id' => $this->base_id,
            'is_parent' => !$this->parent_id,
            'media' => collect($this->media)->map(fn($md) => $md->path . $md->name)->toArray(),
            'options' => OptionResource::collection($this->whenLoaded('options')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'children' => self::collection($this->children?->unique())

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
