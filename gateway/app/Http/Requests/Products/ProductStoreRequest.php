<?php

namespace App\Http\Requests\Products;

use App\Models\Tenants\Box;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class ProductStoreRequest
 * @package App\Http\Requests\Products
 * @OA\Schema(
 *     schema="ProductStoreRequest",
 *     title="Custom store Products Request"
 *
 * )
 */
class ProductStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="margin_type", default="", description="margin_type", property="margin_type"),
     * @OA\Property(format="string", title="discount_value", default=20, description="discount_value", property="discount_value"),
     * @OA\Property(format="string", title="discount_type", default="", description="discount_type", property="discount_type"),
     * @OA\Property(format="string", title="price", default=50, description="price", property="price"),
     * @OA\Property(format="string", title="sale_start_at", default="2022-06-28", description="sale_start_at", property="sale_start_at"),
     * @OA\Property(format="string", title="sale_end_at", default="2022-06-30", description="sale_end_at", property="sale_end_at"),
     * @OA\Property(format="string", title="free", default=false, description="free", property="free"),
     * @OA\Property(property="properties",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="props", type="string", example=null),
     *          @OA\Property(type="array", property="template", @OA\Items(
     *              @OA\Property(property="mode", type="string", example="DesignProviderTemplate"),
     *              @OA\Property(property="id", type="int64", example=1, description="number came from desigProvider"),
     *          )),
     *          @OA\Property(property="validations", type="string", example="[]")
     *        )
     *     ),
     * @OA\Property(format="string", title="stock_product", default=false, description="stock_product", property="stock_product"),
     * @OA\Property(format="string", title="excludes", default=false, description="excludes", property="excludes"),
     * @OA\Property(format="string", title="combination", default=false, description="combination", property="combination"),
     * @OA\Property(format="string", title="products", default="[]", description="products", property="products"),
     * @OA\Property(format="string", title="vat_id", default="", description="vat_id", property="vat_id"),
     * @OA\Property(format="string", title="unit_id", default="1", description="unit_id", property="unit_id"),
     * @OA\Property(format="string", title="parent_id", default=5, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="brand_id", default=5, description="brand_id", property="brand_id"),
     * @OA\Property(format="string", title="category_id", default=1, description="category_id", property="category_id"),
     * @OA\Property(format="string", title="iso", default=5, description="iso", property="iso"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=2, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=6, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="2021-6-28", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="expire_date", default="2022-6-28", description="expire_date", property="expire_date"),
     * @OA\Property(format="string", title="expire_after", default=20, description="expire_after", property="expire_after"),
     * @OA\Property(format="string", title="low_qty_threshold", default=9, description="low_qty_threshold", property="low_qty_threshold"),
     * @OA\Property(format="string", title="high_qty_threshold", default=9, description="high_qty_threshold", property="high_qty_threshold"),
     * @OA\Property(format="string", title="open_stock", default="", description="open_stock", property="open_stock"),
     * @OA\Property(format="string", title="ean", default="", description="ean", property="ean"),
     * @OA\Property(format="string", title="stock", default="[]", description="stock", property="stock"),
     * @OA\Property(format="string", title="variations", default="[]", description="equals true if product has variation", property="variations"),
     * @OA\Property(type="array", property="translations", @OA\Items(ref="#/components/schemas/LanguageResource")),
     * @OA\Property(format="string", title="media", default="", description="media", property="media"),
     */

    public function rules()
    {
        return [
            'name' => 'required',
            'description' => 'string|nullable|max:255',
            'art_num' => 'string|nullable|max:255',
            'sort' => 'integer|nullable',

            'margin_value' => 'integer|nullable',
            'margin_type' => 'in:fixed,percentage|nullable',
            'discount_value' => 'integer|nullable',
            'discount_type' => 'in:fixed,percentage|nullable',

            'price' => 'integer|nullable|min:0',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date',

            'free' => 'boolean',

            'properties' => 'array|required',
            'properties.validations' => 'array',
            'properties.template' => 'array|nullable',
            'properties.template.id' => 'integer|exists:design_provider_templates,id',
            'properties.template.mode' => 'string|in:DesignProviderTemplate',
            'properties.props' => 'array|nullable',

            'stock_product' => 'required|boolean',
            'excludes' => 'required|boolean',
            'variation' => 'required|boolean',
            'combination' => 'required|boolean',
            'products' => 'array|required_if:combination,true',
            'products.*.sku_id' => 'required|exists:skus,id',
            'vat_id' => 'integer|nullable|exists:vats,id',
            'unit_id' => 'string|exists:units,id',

            'parent_id' => 'nullable|integer|exists:products,row_id',
            'brand_id' => 'nullable|integer|exists:brands,row_id',
            'category_id' => 'required|exists:categories,row_id',
            'iso' => 'required',

            'published' => 'nullable|boolean',
            'created_by' => 'nullable|integer|exists:users,id',
            'published_by' => 'nullable|integer|exists:users,id',
            'published_at' => 'nullable',

            'expire_date' => 'date|nullable',
            'expire_after' => 'integer|nullable',


            'low_qty_threshold' => 'integer|nullable',
            'high_qty_threshold' => 'integer|nullable',
            'open_stock' => 'date|nullable',
            'ean' => 'nullable|string:max:255', // validate based on the ean code


            'stock' => 'required_if:stock_product,true|array',
            'stock.qty' => 'required_if:stock_product,true',
            'stock.location_id' => 'integer|nullable|exists:locations,id',


            'variations' => 'required_if:variation,true|array',
            'variations.*.id' => 'required_if:variation,true|exists:boxes,row_id',
            'variations.*.appendage' => 'required_if:variation,true|boolean',
            'variations.*.options' => 'required_if:variation,true|array',
            'variations.*.options.*.id' => 'required_if:variation,true|integer|exists:options,row_id',
            'variations.*.options.*.ean' => 'string:max:255', // validate based on the ean code
            'variations.*.options.*.price' => 'integer|nullable|min:0',
            'variations.*.options.*.incremental' => 'boolean|nullable',
            'variations.*.options.*.incremental_by' => 'integer|nullable',
            'variations.*.options.*.default_selected' => 'boolean|nullable',
            'variations.*.options.*.switch_price' => 'boolean|nullable',
            'variations.*.options.*.expire_date' => 'date|nullable',
            'variations.*.options.*.appendage' => 'boolean|nullable',
            'variations.*.options.*.expire_after' => 'integer|nullable',
            'variations.*.options.*.child' => 'array|nullable',
            'variations.*.options.*.child.*.id' => 'integer|exists:options,row_id',

            'translation' => 'nullable|array',
            'translation.*.iso' => 'string|exists:languages,iso',
            'translation.*.name' => 'nullable|string|max:200',
            'translation.*.description' => 'nullable|string|max:255',

            'media' => 'array|nullable',
            'media.*' => 'string|nullable',


        ];
    }

    protected function prepareForValidation()
    {
        $user = Auth::user();
        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();
        $this->merge(array_merge([
            'iso' => App::getLocale(),
            'free' => $this->free ?? false,
            'stock_product' => $this->stock_product ?? false,
            'created_by' => $user?->id,
            'published' => $this->published ?? false,
            'combination' => $this->combination ?? false,
            'properties' => [
                'validations' => $this->properties['validations'] ?? [],
                'template' => $this->properties['template'] ?? [],
                'props' => optional($this->properties)['props'],
            ],
            'excludes' => $this->excludes ?? false,
            'variation' => $this->variation ?? false,
            'published_by' => match ($this->published) {
                null, true => $user?->id,
                false => null
            },
            'published_at' => match ($this->published) {
                null, true => Carbon::now(),
                false => null
            },
            'variations' => collect($this->variations)->map(fn($v) => [
                'id' => $v['id'],
                'options' => $v['options'],
                'appendage' => is_null(optional($v)['appendage']) ?
                    (bool)Box::where('row_id', $v['id'])->first('appendage')?->appendage :
                    $v['appendage'],
            ])->toArray()
        ], $translation));

    }
}
