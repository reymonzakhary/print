<?php

namespace App\Http\Resources\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PermissionResource
 * @package App\Http\Resources\Permissions
 * @OA\Schema(
 * )
 */
class PermissionResource extends JsonResource
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
        return tap(new PermissionResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="name", default="create-order", description="name", property="name"),
     * @OA\Property(format="string", title="namespace", default="orders", description="namespace", property="namespace"),
     * @OA\Property(format="string", title="area", default="default", description="area", property="area"),
     * @OA\Property(format="string", title="display_name", default="Create Order", description="display_name", property="display_name"),
     * @OA\Property(format="string", title="description", default="user can Create Order", description="description", property="description"),
     * @OA\Property(format="date", title="created at", default="2021-09-08T12:19:37.000000Z", description="permtion created date", property="created_at"),
     * @OA\Property(format="date", title="updated at", default="2021-09-08T12:19:37.000000Z", description="permtion last time  updated date", property="updated_at"),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'namespace' => $this->namespace,
            'area' => $this->area,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
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
