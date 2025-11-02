<?php

namespace App\Http\Resources\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PermissionIndexResource
 * @package App\Http\Resources\Permissions
 * @OA\Schema(
 *     schema="PermissionIndexResource",
 *     title="Get All Permissions"
 *
 * )
 */
class PermissionIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(format="string", title="id", default="", description="id", example="1", property="id"),
     * @OA\Property(format="string", title="name", default="", description="name", example="auth", property="name"),
     * @OA\Property(format="string", title="slug", default="", description="slug", example="auth", property="slug"),
     * @OA\Property(format="string", title="sort", default="", description="sort", example="0", property="sort"),
     * @OA\Property(format="string", title="icon", default="", description="icon", example="null", property="icon"),
     * @OA\Property(format="string", title="created_at", default="", description="created_at", example="2021-12-02T11:51:02.000000Z", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="", description="updated_at", example="2021-12-02T11:51:02.000000Z", property="updated_at"),
     * @OA\Property(type="array", property="area", @OA\Items(ref="#/components/schemas/PermissionsArea")),
     */
    /**
     * @OA\Schema(
     *  schema="PermissionsArea",
     *  title="Permissions Area",
     * 	@OA\Property(property="default",type="array" ,example="default",
     *       @OA\Items(
     *          @OA\Property(property="id", type="string", example="1"),
     *          @OA\Property(property="name", type="string", example="auth-access"),
     *          @OA\Property(property="namespace", type="string", example="auth"),
     *          @OA\Property(property="area", type="string", example="default"),
     *          @OA\Property(property="display_name", type="string", example="access"),
     *          @OA\Property(property="description", type="string", example="Access Auth"),
     *          @OA\Property(property="created_at", type="string", example="2021-12-02 12:51:02"),
     *          @OA\Property(property="updated_at", type="string", example="2021-12-02 12:51:02")
     *        )
     *     ),
     * 	@OA\Property(property="status1",type="string"),
     * )
     */
    public function toArray($request)
    {

        return array_merge([
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "sort" => $this->sort,
            "icon" => $this->icon,
        ], [
            'area' => $this->permissions->groupBy('area'),
        ]);

    }
}
