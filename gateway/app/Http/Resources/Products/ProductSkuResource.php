<?php

namespace App\Http\Resources\Products;

use App\Models\Tenants\Box;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class ProductSkuResource
 * @package App\Http\Resources\Products
 * @OA\Schema(
 *     schema="ProductSkuResource",
 *     title="Product SKU Resource"
 *
 * )
 */
class ProductSkuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="int64", title="ean", default=2166074441502, description="ean", property="ean"),
     * @OA\Property(format="int64", title="sku", default="ab48b125-56a1-48e9-a3f9-264cca0e1307", description="sku", property="sku"),
     * @OA\Property(format="int64", title="low_qty_threshold", default=null, description="low_qty_threshold", property="low_qty_threshold"),
     * @OA\Property(format="string", title="price", default=50, description="price", property="price"),
     * @OA\Property(format="string", title="display_price", default="200 EGP", description="display_price", property="display_price"),
     * @OA\Property(format="string", title="stock_count", default=0, description="stock_count", property="stock_count"),
     * @OA\Property(format="string", title="in_stock", default=false, description="in_stock", property="in_stock"),
     * @OA\Property(format="string", title="published", default=false, description="published", property="published"),
     * @OA\Property(format="string", title="media", default="[]", description="media", property="media"),
     * @OA\Property(format="string", title="product", default="[]", description="product", property="product"),
     * @OA\Property(format="string", title="appendage", default="[]", description="appendage", property="appendage"),
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->product->name,
            'ean' => $this->ean,
            'sku' => $this->sku,
            'low_qty_threshold' => $this->low_qty_threshold,
            'price' => $this->price->amount(),
            'display_price' => $this->price->format(),
            'stock_count' => $this->stockCount(),
            'in_stock' => $this->inStock(),
            'published' => $this->published,
            'media' => collect($this->media)->map(fn($md) => $md->path . $md->name)->toArray(),
            'product' => ProductSkuVariationResource::collection(
                collect($this->variations)
                    ->map(fn($variation) => $variation->ancestorsAndSelf->sortBy('sort'))->flatten()
            ),
            'appendage' => collect($this->product->appendage->groupBy('box.name'))->map(function ($v, $k) {
                $v->load('option', 'option.children');
                return array_merge(
                    Box::where([['name', $k], ['iso', app()->getLocale()]])->first()->toArray(),
                    [
                        'options' => ProductVariationResource::collection($v->unique('option_id'))
                    ]
                );
            })->values()
        ];
    }
}
