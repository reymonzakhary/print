<?php

namespace App\Http\Resources\Roles;

use App\Http\Resources\Permissions\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoleResource
 * @package App\Http\Resources\Roles
 * @OA\Schema(
 *     schema="RoleResource",
 *     title="Role Resource"
 *
 * )
 */
class RoleResource extends JsonResource
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
        return tap(new RoleResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="display_name", default="", description="display_name", property="display_name"),
     * @OA\Property(format="string", title="description", default="Use Role", description="description", property="description"),
     * @OA\Property(type="array", property="permissions", @OA\Items(ref="#/components/schemas/PermissionResource")),
     * @OA\Property(format="date", title="created at", default="2021-09-08T12:19:37.000000Z", description="name", property="created_at"),
     * @OA\Property(format="date", title="updated at", default="2021-09-08T12:19:37.000000Z", description="name", property="updated_at"),
     */
    public function toArray($request)
    {

        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions'))->hide($this->defaultHide),
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
