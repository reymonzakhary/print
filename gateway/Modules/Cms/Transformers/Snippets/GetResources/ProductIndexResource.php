<?php

namespace Modules\Cms\Transformers\Snippets\GetResources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;
use Modules\Cms\Foundation\Traits\HasMedia;

class ProductIndexResource extends JsonResource
{
    use HasMedia;
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="string", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="slug", default="shoes", description="slug", property="slug"),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="boolean", title="art_num", default="2", description="art_num", property="art_num"),
     * @OA\Property(format="int64", title="sort", default=5, description="sort", property="sort"),
     * @OA\Property(format="string", title="brand_id", default=5, description="brand_id", property="brand_id"),
     * @OA\Property(format="int64", title="margin_value", default=1, description="margin_value", property="margin_value"),
     * @OA\Property(format="string", title="margin_type", default=null, description="margin_type", property="margin_type"),
     * @OA\Property(format="string", title="discount_value", default=20, description="discount_value", property="discount_value"),
     * @OA\Property(format="string", title="discount_type", default=null, description="discount_type", property="discount_type"),
     * @OA\Property(format="string", title="price", default=null, description="price", property="price"),
     * @OA\Property(format="string", title="sale_start_at", default="2022-06-28T11:59:11.789201Z", description="sale_start_at", property="sale_start_at"),
     * @OA\Property(format="string", title="sale_end_at", default="2022-06-30T11:59:11.789201Z", description="sale_end_at", property="sale_end_at"),
     * @OA\Property(format="string", title="free", default=false, description="free", property="free"),
     * @OA\Property(property="properties",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="validations", type="string", example="[]"),
     *          @OA\Property(property="templates", type="string", example="[]"),
     *          @OA\Property(property="props", type="string", example=null)
     *        )
     *     ),
     * @OA\Property(format="string", title="stock_product", default=false, description="stock_product", property="stock_product"),
     * @OA\Property(format="string", title="variation", default=false, description="equals true if product has variation", property="variation"),
     * @OA\Property(format="string", title="combination", default=false, description="combination", property="combination"),
     * @OA\Property(format="string", title="excludes", default=false, description="excludes", property="excludes"),
     * @OA\Property(format="string", title="vat_id", default=null, description="vat_id", property="vat_id"),
     * @OA\Property(format="string", title="unit_id", default="1", description="unit_id", property="unit_id"),
     * @OA\Property(format="string", title="products", default="[]", description="products", property="products"),
     * @OA\Property(format="string", title="iso", default="en", description="iso", property="iso"),
     * @OA\Property(format="string", title="published", default=false, description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=1, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="2022-08-23T16:04:43.733789Z", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="expire_date", default="2022-08-23T16:04:43.733789Z", description="expire_date", property="expire_date"),
     * @OA\Property(format="string", title="expire_after", default=20, description="expire_after", property="expire_after"),
     * @OA\Property(format="string", title="media", default="[]", description="media", property="media"),
     * @OA\Property(format="string", title="sku", default=null, description="sku", property="sku"),
     * @OA\Property(format="string", title="ean", default=null, description="ean", property="ean"),
     * @OA\Property(format="string", title="stock_count", default=null, description="stock_count", property="stock_count"),
     * @OA\Property(format="string", title="in_stock", default=null, description="in_stock", property="in_stock"),
     * @OA\Property(format="string", title="high_qty_threshold", default=null, description="high_qty_threshold", property="high_qty_threshold"),
     * @OA\Property(format="string", title="low_qty_threshold", default=null, description="low_qty_threshold", property="low_qty_threshold"),
     * @OA\Property(format="string", title="open_stock", default="", description="open_stock", property="open_stock"),
     * @OA\Property(format="string", title="created_at", default="2022-08-23T16:04:43.733789Z", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="2022-08-23T16:04:43.733789Z", description="updated_at", property="updated_at"),
     * @OA\Property(format="string", title="variations", default="[]", description="variations", property="variations"),
     */
    public function toArray($request)
    {
        return [
            'id' => $this->row_id,
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
            /** @todo check the product combinations */
            // 'products' => $this->combination ?
                // ProductResource::collection($this->sku?->childrens ?? []) :
                // [],
            'brand' => CategoryResource::make($this->whenLoaded('brand')),
            'category' => CategoryResource::make($this->whenLoaded('category')),

            'iso' => $this->iso,

            'published' => $this->published,
            'created_by' => $this->created_by,
            'published_by' => $this->published_by,
            'published_at' => $this->published_at,
            'expire_date' => $this->expire_date,
            'expire_after' => $this->expire_after,

            'media' => collect($this->media)->map(fn($fm) => $this->getImageUrlFromFileManagerModel($fm))->toArray(),

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
            'hasBlueprint' => $this->hasBlueprint,
            'queueable' => $this->pivot?->queueable,
            'step' => $this->pivot?->step
        ];
    }
}
