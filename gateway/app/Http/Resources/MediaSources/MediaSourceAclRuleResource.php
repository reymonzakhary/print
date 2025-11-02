<?php

namespace App\Http\Resources\MediaSources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaSourceAclRuleResource
 * @package App\Http\Resources\MediaSources
 * @OA\Schema(
 * )
 */
class MediaSourceAclRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(format="int64", title="id", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="user_id", default="", description="name", example="1", property="name"),
     * @OA\Property(format="string", title="disk", default="", description="slug", property="slug"),
     * @OA\Property(property="path", type="string"),
     * @OA\Property(property="access", type="string"),
     * @OA\Property(property="create_at", type="string"),
     * @OA\Property(property="updated_at", type="string")
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'disk' => $this->disk,
            'path' => $this->path,
            'access' => $this->access,
            'create_at' => $this->create_at,
            'updated_at' => $this->updated_at
        ];
    }
}
