<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SkuUpdateRequest
 * @package App\Http\Requests\Products
 * @OA\Schema(
 *     schema="SkuUpdateRequest",
 *     title="Custom update sku Products Request"
 *
 * )
 */
class SkuUpdateRequest extends FormRequest
{
    private $option;

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
            'price' => 'integer',
            'ean' => 'string',
            'low_qty_threshold' => 'integer',
            'high_qty_threshold' => 'integer',
            'open_stock' => 'string',
            "sale_start_at" => 'string',
            "sort" => "integer",
            "sale_end_at" => "string",
            "parent_id" => "integer",
            "product_id" => "integer",
            'stock' => 'required_if:stock_product,true|array',
            'stock.qty' => 'integer|nullable',
            'published' => 'boolean',
            'stock.location_id' => 'integer|nullable|exists:locations,id',
            'media' => 'array|nullable',
            'media.*' => 'string|nullable',
            'variation' => 'array' . (optional($this->option)->input_type === 'file' ? '|required' : ''),
            'variation.single' => 'boolean',
            'variation.upto' => 'integer|required_if:' . optional($this->option)->input_type . ",file",
            'variation.mime_type' => 'string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->option = optional(optional(optional($this->sku)->variations)->first())->option;
        $this->merge([
            'variation.single' => $this->variation['single'] ?? optional($this->option)->single,
            'variation.upto' => $this->variation['upto'] ?? optional($this->option)->upto,
            'variation.mime_type' => $this->variation['mime_type'] ?? optional($this->option)->mime_type,
        ]);
    }
}
