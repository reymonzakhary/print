<?php

namespace App\Http\Resources\Shops;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ShopProductResource
 * @package App\Http\Resources\ShopProductResource
 * @OA\Schema(
 *     schema="ShopProductResource",
 *     title="Shop Cutsom Product Resource"
 *
 * )
 */
class ShopProductResource extends JsonResource
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
     * @OA\Property(type="array", property="children", @OA\Items(ref="#/components/schemas/ShopProductResource"))
     */
    public function toArray($request)
    {
        return [
            'id' => $this?->sku?->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'art_num' => $this->art_num,
            'sort' => $this->sort,
            'brand_id' => $this->brand_id,
            'margin_value' => $this->margin_value,
            'margin_type' => $this->margin_type,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,

            'price' => $this->sku?->price->amount(),
            'display_price' => $this->sku?->price->format(),

            'sale_start_at' => $this->sale_start_at,
            'sale_end_at' => $this->sale_end_at,


            'free' => $this->free,
            'properties' => $this->properties,

            'stock_product' => $this->stock_product,
            'variation' => $this->variation,
            'combination' => $this->combination,
            'excludes' => $this->excludes,
            'vat_id' => $this->vat_id,
            'unit_id' => $this->unit_id,
            'products' => $this->combination ?
                ShopProductResource::collection($this->sku?->childrens()->get() ?? []) :
                [],
            'brand' => ShopCategoryResource::make($this->whenLoaded('brand')),
            'category' => ShopCategoryResource::make($this->whenLoaded('category')),

            'iso' => $this->iso,
//            'variations' => !$this->variations
//                ? []
//                : collect(
//                    $this->variations->groupBy('box.name')
//                )->map(function($v,$k) {
//                    $box = Box::where('name', $k)->first();
//                    return [
//                            'id' => $box->row_id,
//                            'name' => $box->name,
//                            'description' => $box->description,
//                            'slug' => $box->slug,
//                            'input_type' => $box->input_type,
//                            'incremental' => $box->incremental,
//                            'select_limit' => $box->select_limit,
//                            'option_limit' => $box->option_limit,
//                            'sqm' => $box->sqm,
//                            'iso' => trim($box->iso),
//                            'base_id' => $box->base_id,
//                            'is_parent' =>  !$box->parent_id,
//                            'media' => collect($box->media)->map(fn($md) => $md->path . $md->name)->toArray(),
//                            'options' => ShopVariationResource::collection($v->unique('option_id'))
//                        ];
//
//                })->values(),
            'published' => $this->published,
            'created_by' => $this->created_by,
            'published_by' => $this->published_by,
            'published_at' => $this->published_at,
            'expire_date' => $this->expire_date,
            'expire_after' => $this->expire_after,

            'media' => collect($this->media)->map(fn($md) => $md->path . '/' . $md->name)->toArray(),

            'sku' => !$this->variation || ($this->variation && !$this->excludes) ? $this->sku?->sku : null,
            'sku_id' => !$this->variation || ($this->variation && !$this->excludes) ? $this->sku?->id : null,
            'ean' => !$this->variation || ($this->variation && !$this->excludes) ? $this->sku?->id ? $this->sku->ean : null : null,

            'stock_count' => $this->sku?->stockCount(),
            'in_stock' => $this->sku?->inStock(),
            'high_qty_threshold' => $this->sku?->high_qty_threshold,
            'low_qty_threshold' => $this->sku?->low_qty_threshold,
            'open_stock' => $this->sku?->open_stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'hasBlueprint' => optional($this)->hasBlueprint,
        ];
    }
}
