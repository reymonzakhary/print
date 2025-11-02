<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ProductVariationUpdateRequest
 * @package App\Http\Requests\Products
 * @OA\Schema(
 *     schema="ProductVariationUpdateRequest",
 *     title="Product Variation Update Request"
 *
 * )
 */
class ProductVariationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @OA\Property(format="int64", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="string", title="price", default=50, description="price", property="price"),
     * @OA\Property(format="string", title="sale_start_at", default="2022-06-28T11:59:11.789201Z", description="sale_start_at", property="sale_start_at"),
     * @OA\Property(format="string", title="sale_end_at", default="2022-06-30T11:59:11.789201Z", description="sale_end_at", property="sale_end_at"),
     * @OA\Property(format="string", title="parent_id", default=5, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="product_id", default=5, description="product_id", property="product_id"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="low_qty_threshold", default=9, description="low_qty_threshold", property="low_qty_threshold"),
     * @OA\Property(format="string", title="high_qty_threshold", default=9, description="high_qty_threshold", property="high_qty_threshold"),
     * @OA\Property(format="string", title="open_stock", default="", description="open_stock", property="open_stock"),
     * @OA\Property(format="string", title="ean", default="", description="ean", property="ean"),
     * @OA\Property(format="string", title="stock", default="[]", description="stock", property="stock"),
     * @OA\Property(format="string", title="stock.qty", default=100, description="stock.qty", property="stock.qty"),
     * @OA\Property(format="string", title="stock.location_id", default=1, description="stock.location_id", property="stock.location_id"),
     * @OA\Property(format="string", title="media", default="[]", description="media", property="media"),
     * @OA\Property(format="string", title="variation", default="[]", description="equals true if product has variation", property="variations"),
     */
    public function rules()
    {
        return [

        ];
    }

}
