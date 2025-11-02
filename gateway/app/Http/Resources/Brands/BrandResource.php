<?php

namespace App\Http\Resources\Brands;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class BrandResource
 * @package App\Http\Resources\Brands
 * @OA\Schema(
 *     schema="BrandResource",
 *     title="Brand Resource"
 *
 * )
 */
class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="nike", description="name", property="name"),
     * @OA\Property(format="string", title="slug", default="Nike", description="slug", property="slug"),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="string", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="string", title="iso", default=1, description="iso", property="iso"),
     * @OA\Property(format="string", title="published", default="2022-06-28T11:59:11.789201Z", description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=1, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="2022-06-28T11:59:11.789201Z", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="media", default="['image.jpg', 'image.jpg']", description="media", property="media"),
     * @OA\Property(format="string", title="created_at", default="2022-06-28T11:59:11.789201Z", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="2022-06-28T11:59:11.789201Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        return [
            'id' => $this->row_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort' => $this->sort,
            'iso' => trim($this->iso),
            'published' => $this->published,
            'created_by' => $this->created_by,
            'published_by' => $this->published_by,
            'published_at' => $this->published_at,
            'media' => collect($this->media)->map(fn($md) => $md->path . $md->name)->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
