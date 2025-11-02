<?php

namespace App\Http\Resources\MediaSources;

use App\Http\Resources\Context\ContextResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaSourceResource
 * @package App\Http\Resources\MediaSources
 * @OA\Schema(
 *     schema="MediaSourceResource",
 *     title="Media Source Resource"
 *
 * )
 */
class MediaSourceResource extends JsonResource
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
        return tap(new MediaSourceResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="name", default="", description="name", example="superadministrator", property="name"),
     * @OA\Property(format="string", title="slug", default="", description="slug", property="slug"),
     * @OA\Property(property="ctx", type="array", @OA\Items(ref="#/components/schemas/ContextResource")),
     * @OA\Property(property="rules", type="array", @OA\Items(ref="#/components/schemas/MediaSourceAclRuleResource"))
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'ctx' => ContextResource::collection($this->whenLoaded('contexts'))->hide($this->withoutFields),
            'rules' => MediaSourceAclRuleResource::collection($this->rules)

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
