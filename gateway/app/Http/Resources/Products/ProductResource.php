<?php

namespace App\Http\Resources\Products;

use App\Models\Tenant\Box;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * Class ProductResource
 * @package App\Http\Resources\Products
 * @OA\Schema(
 *     schema="ProductResource",
 *     title="Product Resource"
 *
 * )
 */
class ProductResource extends ProductIndexResource
{
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
        return match ((bool)$this->variation) {
            false => $this->singleProductResponse($request),
            true => match ((bool)$this->excludes) {
                (bool)$this->stock_product && (bool)!$this->excludes =>
                $this->productVariationWithOutStockResponse($request),
                (bool)!$this->stock_product && (bool)!$this->excludes =>
                $this->productVariationWithStockResponse($request),
                (bool)!$this->stock_product && (bool)$this->excludes =>
                $this->productCombinationWithOutStockResponse($request),
                (bool)$this->stock_product && (bool)$this->excludes =>
                $this->productCombinationWithStockResponse($request),
            },
        };
    }

    /**
     * @param $request
     * @return array
     */
    protected function singleProductResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => []
        ]);
    }

    /**
     * @param $request
     * @return array
     */
    protected function productVariationWithOutStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => collect(
                $this->variations->groupBy('box.name')
            )->map(function ($v, $k) {
                $box = Box::where('name', $k)->first();
                return [
                    'id' => $box->row_id,
                    'name' => $box->name,
                    'description' => $box->description,
                    'slug' => $box->slug,
                    'input_type' => $box->input_type,
                    'incremental' => $box->incremental,
                    'select_limit' => $box->select_limit,
                    'option_limit' => $box->option_limit,
                    'sqm' => $box->sqm,
                    'iso' => trim($box->iso),
                    'base_id' => $box->base_id,
                    'is_parent' => !$box->parent_id,
                    'media' => collect($box->media)->map(fn($md) => $md->path . $md->name)->toArray(),
                    'options' => ProductOptionResource::collection($v->unique('option_id')),
                ];
            })->values()

        ]);
    }

    /**
     * @param $request
     * @return array
     */
    protected function productVariationWithStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => collect(
                $this->variations->groupBy('box.name')
            )->map(function ($v, $k) {
                $box = Box::where('name', $k)->first();
                return [
                    'id' => $box->row_id,
                    'name' => $box->name,
                    'description' => $box->description,
                    'slug' => $box->slug,
                    'input_type' => $box->input_type,
                    'incremental' => $box->incremental,
                    'select_limit' => $box->select_limit,
                    'option_limit' => $box->option_limit,
                    'sqm' => $box->sqm,
                    'iso' => trim($box->iso),
                    'base_id' => $box->base_id,
                    'is_parent' => !$box->parent_id,
                    'media' => collect($box->media)->map(fn($md) => $md->path . $md->name)->toArray(),
                    'options' => ProductOptionResource::collection($v->unique('option_id')),
                ];
            })->values()

        ]);
    }


    protected function productCombinationWithOutStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => ProductSkuResource::collection($this->skus()
                ->with([
                    'variations.ancestorsAndSelf',
                    'variations.ancestorsAndSelf.option',
                    'variations.ancestorsAndSelf.box',
                    'variations.ancestorsAndSelf.option.children',
                    'variations.ancestorsAndSelf.product',
                ])
                ->paginate(request()->perPage ?? 10))
        ]);
    }

    protected function productCombinationWithStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => ProductSkuResource::collection($this->skus()
                ->with([
                    'variations.ancestorsAndSelf',
                    'variations.ancestorsAndSelf.option',
                    'variations.ancestorsAndSelf.box',
                    'variations.ancestorsAndSelf.option.children',
                    'variations.ancestorsAndSelf.product',
                    'stock'
                ])
                ->paginate(request()->per_page ?? 10))
        ]);
    }
}
