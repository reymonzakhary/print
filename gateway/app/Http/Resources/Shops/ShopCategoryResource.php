<?php

namespace App\Http\Resources\Shops;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ShopCategoryResource
 * @package App\Http\Resources\ShopCategoryResource
 * @OA\Schema(
 *     schema="ShopCategoryResource",
 *     title="Shop Cutsom Category Resource"
 *
 * )
 */
class ShopCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="slug", default="Shoes", description="slug", property="slug"),
     * @OA\Property(format="string", title="description", default="shoes", description="description", property="description"),
     * @OA\Property(format="string", title="iso", default=1, description="iso", property="iso"),
     * @OA\Property(format="string", title="sort", default=true, description="sort", property="sort"),
     * @OA\Property(format="string", title="row_id", default=1, description="row_id", property="row_id"),
     * @OA\Property(format="string", title="depth", default=100, description="depth", property="depth"),
     * @OA\Property(format="string", title="path", default="storage", description="path", property="path"),
     * @OA\Property(format="string", title="slug_path", default="Storage", description="slug_path", property="slug_path"),
     * @OA\Property(format="string", title="base_id", default=1, description="base_id", property="base_id"),
     * @OA\Property(format="string", title="has_children", default=true, description="has_children", property="has_children"),
     * @OA\Property(format="string", title="is_parent", default=true, description="is_parent", property="is_parent"),
     * @OA\Property(format="string", title="parent_id", default=2, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="media", default="[]", description="media", property="media"),
     * @OA\Property(format="string", title="margin_value", default=2, description="margin_value", property="margin_value"),
     * @OA\Property(format="string", title="margin_type", default="top", description="margin_type", property="margin_type"),
     * @OA\Property(format="string", title="discount_value", default=20, description="discount_value", property="discount_value"),
     * @OA\Property(format="string", title="discount_type", default="percentage", description="discount_type", property="discount_type"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="published_at", default="today", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="published_by", default=1, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="created_by", default=2, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="created_at", default="2022-06-28T11:59:11.789201Z", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="2022-06-28T11:59:11.789201Z", description="updated_at", property="updated_at"),
     * @OA\Property(type="array", property="children", @OA\Items(ref="#/components/schemas/ShopCategoryResource"))
     */
    public function toArray($request)
    {
        return [
            'id' => $this->row_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort' => $this->sort,
            'iso' => trim($this->iso),
            'base_id' => $this->base_id,
            'margin_value' => $this->margin_value,
            'margin_type' => $this->margin_type,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,
            'published' => $this->published,
            'created_by' => $this->created_by,
            'published_by' => $this->published_by,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'slug_path' => $this->slug_path,
//            'has_children' => (bool) $this->HasChildren(),
//            'is_parent' =>  !$this->parent_id,

            'media' => collect($this->media)->map(fn($md) => $md->path . '/' . $md->name)->toArray(),
            'children' => self::collection($this->whenLoaded('children'))

        ];
    }
}
