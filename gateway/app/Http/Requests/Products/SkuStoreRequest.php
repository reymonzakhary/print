<?php

namespace App\Http\Requests\Products;

use App\Models\Tenants\Box;
use App\Models\Tenants\Option;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

/**
 * Class SkuStoreRequest
 * @package App\Http\Requests\Products
 * @OA\Schema(
 *     schema="SkuStoreRequest",
 *     title="Custom store sku Products Request"
 *
 * )
 */
class SkuStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="boolean", title="art_num", default="2", description="art_num", property="art_num"),
     * @OA\Property(format="int64", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="int64", title="margin_value", default=1, description="margin_value", property="margin_value"),
     * @OA\Property(format="string", title="margin_type", default="left", description="margin_type", property="margin_type"),
     * @OA\Property(format="string", title="discount_value", default=20, description="discount_value", property="discount_value"),
     * @OA\Property(format="string", title="discount_type", default="mgr", description="discount_type", property="discount_type"),
     * @OA\Property(format="string", title="price", default=50, description="price", property="price"),
     * @OA\Property(format="string", title="sale_start_at", default="2022-06-28T11:59:11.789201Z", description="sale_start_at", property="sale_start_at"),
     * @OA\Property(format="string", title="sale_end_at", default="2022-06-30T11:59:11.789201Z", description="sale_end_at", property="sale_end_at"),
     * @OA\Property(format="string", title="free", default=false, description="free", property="free"),
     * @OA\Property(format="string", title="properties", default="['big size', 'good material']", description="properties", property="properties"),
     * @OA\Property(format="string", title="properties.validations", default="[]", description="properties.validations", property="properties.validations"),
     * @OA\Property(format="string", title="properties.templates", default="[]", description="properties.templates", property="properties.templates"),
     * @OA\Property(format="string", title="stock_product", default=true, description="stock_product", property="stock_product"),
     * @OA\Property(format="string", title="excludes", default=false, description="excludes", property="excludes"),
     * @OA\Property(format="string", title="combination", default=false, description="combination", property="combination"),
     * @OA\Property(format="string", title="products", default="['airmax', 'airjordan']", description="products", property="products"),
     * @OA\Property(format="string", title="vat_id", default=1, description="vat_id", property="vat_id"),
     * @OA\Property(format="string", title="unit_id", default=5, description="unit_id", property="unit_id"),
     * @OA\Property(format="string", title="parent_id", default=5, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="brand_id", default=5, description="brand_id", property="brand_id"),
     * @OA\Property(format="string", title="category_id", default=5, description="category_id", property="category_id"),
     * @OA\Property(format="string", title="iso", default=5, description="iso", property="iso"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=2, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=6, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="today", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="expire_date", default="tomorrow", description="expire_date", property="expire_date"),
     * @OA\Property(format="string", title="expire_after", default=20, description="expire_after", property="expire_after"),
     * @OA\Property(format="string", title="low_qty_threshold", default=9, description="low_qty_threshold", property="low_qty_threshold"),
     * @OA\Property(format="string", title="high_qty_threshold", default=9, description="high_qty_threshold", property="high_qty_threshold"),
     * @OA\Property(format="string", title="open_stock", default="", description="open_stock", property="open_stock"),
     * @OA\Property(format="string", title="ean", default="", description="ean", property="ean"),
     * @OA\Property(format="string", title="stock", default="['adidas', 'nike']", description="stock", property="stock"),
     * @OA\Property(format="string", title="variations", default="[]", description="equals true if product has variation", property="variations"),
     * @OA\Property(type="array", property="translations", @OA\Items(ref="#/components/schemas/LanguageResource")),
     * @OA\Property(format="string", title="media", default="", description="media", property="media"),
     */
    public function rules()
    {
        return [

            'price' => 'integer|nullable|min:0',
            'stock_product' => 'required|boolean',
            'excludes' => 'required|boolean',
            'variation' => 'required|boolean',
            'combination' => 'required|boolean',
            'low_qty_threshold' => 'integer|nullable',
            'high_qty_threshold' => 'integer|nullable',
            'open_stock' => 'date|nullable',
            'ean' => 'nullable|string:max:255', // validate based on the ean code
            "sale_start_at" => 'string',
            "sort" => "integer|nullable",
            "sale_end_at" => "string",
            "parent_id" => "integer",
            "product_id" => "integer",

            'stock' => 'required_if:stock_product,true|array',
            'stock.qty' => 'integer|nullable',
            'stock.location_id' => 'integer|nullable|exists:locations,id',

            'single' => 'required_if:input_type,file|boolean',
            'upto' => 'integer|required_if:single,false',
            'mime_type' => 'string|required_if:input_type,file|in:pdf,xls,xlsx',

            'variations' => 'required_if:variation,true|array',
            'variations.*.id' => 'required_if:variation,true|exists:boxes,row_id',
            'variations.*.appendage' => 'required_if:variation,true|boolean',
            'variations.*.options' => 'required_if:variation,true|array',
            'variations.*.options.*.option_id' => 'required_if:variation,true|integer|exists:options,row_id',
            'variations.*.options.*.ean' => 'string:max:255', // validate based on the ean code
            'variations.*.options.*.price' => 'nullable|min:0',

            'variations.*.options.*.incremental' => 'boolean|nullable',
//            'variations.*.options.*.single' => 'boolean|nullable',
            'variations.*.options.*.upto' => 'required_if:variations.*.options.*.single,false',
            'variations.*.options.*.mime_type' => 'string||nullable',

            'variations.*.options.*.incremental_by' => 'integer|nullable',
            'variations.*.options.*.default_selected' => 'boolean|nullable',
            'variations.*.options.*.switch_price' => 'boolean|nullable',
            'variations.*.options.*.expire_date' => 'date|nullable',
            'variations.*.options.*.expire_after' => 'integer|nullable',

            'variations.*.options.*.child' => 'nullable',
            'variations.*.options.*.child.*.id' => 'integer|exists:options,row_id',

            'media' => 'array|nullable',
            'media.*' => 'string|nullable',

        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(
            [
                'iso' => App::getLocale(),
                'stock_product' => $this->stock_product ?? false,
                'combination' => $this->combination ?? false,
                'excludes' => $this->excludes ?? false,
                'variation' => $this->variation ?? false,
                'variations' => collect($this->variations)->unique()->map(function ($v) {
                    return [
                        'id' => $v['id'],
                        'options' => collect($v['options'])->map(
                            function ($option) {
                                if (optional($option)['id']) {
                                    $option = Option::where('row_id', $option['id'])->first()->toArray();
                                    $option['option_id'] = $option['id'];
                                    $option['price'] = $option['price']->amount();
                                    return $option;
                                } else {
                                    return $option;
                                }
                            })->toArray(),
                        'appendage' => is_null(optional($v)['appendage']) ?
                            (bool)Box::where('row_id', $v['id'])->first('appendage')?->appendage :
                            $v['appendage'],
                    ];

                })->toArray()
            ]
        );

    }
}
