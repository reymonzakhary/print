<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories\Printing;

use App\Facades\Context;
use App\Http\Requests\MediaImageValidatorTrait;
use App\Models\Tenant\Language;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateCategoryRequest
 * @package App\Http\Resources\Categories
 * @OA\Schema(
 *     schema="UpdateCategoryRequest",
 *     title="Print Category Request"
 *
 * )
 */
final class UpdateCategoryRequest extends FormRequest
{
    use MediaImageValidatorTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    /**
     * @OA\Property(format="int64", title="linked", description="linked", property="linked" ,example="null | 61b1b520a50498808633c525"),
     * @OA\Property(format="string", title="iso", default="", description="iso", example="EN", property="iso"),
     * @OA\Property(format="string", title="sort", default="0", description="sort", property="sort"),
     * @OA\Property(format="string", title="name", description="name", property="name", example="Posters"),
     * @OA\Property(format="string", title="ref_id", description="nullable", property="ref_id", example="Posters"),
     * @OA\Property(format="string", title="ref_category_id", description="nullable", property="ref_category_id", example="Posters"),
     * @OA\Property(format="string", title="ref_category_name", description="nullable", property="ref_category_name", example="Posters"),
     * @OA\Property(type="array", property="countries", @OA\Items(ref="#/components/schemas/countriesArea")),
     * @OA\Property(format="string", title="description", description="description", property="description", example="Posters b"),
     * @OA\Property(format="string", title="sku", description="sku", property="sku", example="qweqe-322132-qweqw"),
     * @OA\Property(format="string", title="published", description="published", property="published", example="true"),
     * @OA\Property(format="string", title="media", description="media", property="media", example="['image.jpg', 'image.jpg']"),
     * @OA\Property(property="price_build",type="array" ,description="[Optinal]",
     *       @OA\Items(
     *          @OA\Property(property="collection", type="boolen", example="Ture"),
     *          @OA\Property(property="full_calculation", type="string", example="False"),
     *          @OA\Property(property="semi_calculation", type="string", example="Ture")
     *        )
     *     ),
     * @OA\Property(property="calculation_method",type="array" ,description="[Optinal if semi_calculation is True]",
     *       @OA\Items(
     *          @OA\Property(property="active", type="boolen", example="Ture"),
     *          @OA\Property(property="name", type="string", example="slide scale"),
     *          @OA\Property(property="slug", type="string", example="slide-scale")
     *        )
     *     ),
     * @OA\Property(property="dlv_days",type="array" ,description="[Optinal if semi_calculation is True]",
     *       @OA\Items(
     *          @OA\Property(property="days", type="boolen", example="1"),
     *          @OA\Property(property="label", type="string", example="Overnight"),
     *          @OA\Property(property="mode", type="string", example="fixed"),
     *          @OA\Property(property="value", type="string", example="1000")
     *        )
     *     ),
     * @OA\Property(property="printing_method",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="name", type="boolen", example="Digital"),
     *          @OA\Property(property="slug", type="string", example="digital"),
     *          @OA\Property(property="from", type="boolen", example="1"),
     *          @OA\Property(property="to", type="string", example="1000"),
     * 	@OA\Property(property="dlv_days",type="array" ,
     *       @OA\Items(
     * 	        @OA\Property(property="availability",type="array" ,
     *              @OA\Items(
     *                  @OA\Property(property="from", type="string", example="0"),
     *                  @OA\Property(property="to", type="string", example="1200")
     *              )
     *          ),
     *          @OA\Property(property="days", type="string", example="1")
     *        )
     *     ),
     * 	@OA\Property(property="qty_build",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="from", type="boolen", example="1"),
     *          @OA\Property(property="to", type="string", example="Overnight"),
     * 	        @OA\Property(property="price",type="array" ,
     *              @OA\Items(
     *                  @OA\Property(property="value", type="boolen", example="1"),
     *                  @OA\Property(property="mode", type="string", example="Overnight"),
     *                  @OA\Property(property="on", type="string", example="fixed")
     *              )
     *             ),
     *          @OA\Property(property="description", type="string", example="1000"),
     *          @OA\Property(property="incremental_by", type="string", example="1000")
     *        )
     *     ),
     *        )
     *     )
     *        )
     *     ),
     * @OA\Property(format="string", title="start_cost", description="start_cost", property="start_cost", example="1000"),
     * @OA\Property(format="date", title="created at", default="2021-09-08T12:19:37.000000Z", description="name", property="created_at"),
     */

    public function rules(): array
    {
        return [
            'sort' => 'nullable|integer',
            'published' => 'required|bool',
            'shareable' => 'nullable|bool',
            'ref_id' => 'nullable',
            'ref_category_id' => 'nullable',
            'ref_category_name' => 'nullable',

            'countries' => 'array',
            'countries.*.name' => 'string',
            'countries.*.iso2' => 'string',
            'countries.*.iso3' => 'string',
            'countries.*.un_code' => 'string',
            'countries.*.currency' => 'array',
            'countries.*.currency.name' => 'string',
            'countries.*.currency.symbol' => 'string',
            'countries.*.currency.iso' => 'string',
            'linked' => 'nullable|string',

            'name' => 'required|string',

            'display_name' => 'required|array|min:1',
            'display_name.*.iso' => 'required|string',
            'display_name.*.display_name' => 'required|string',

            'system_key' => 'required|string',
            'description' => 'nullable|string',
            'sku' => 'nullable|string',

            'media' => 'nullable|array',
            'media.*' => ['string', $this->validateMediaItemsAsImages(...)],

            'price_build' => 'array',
            'price_build.collection' => 'boolean',
            'price_build.semi_calculation' => 'boolean',
            'price_build.full_calculation' => 'boolean',
            'price_build.external_calculation' => 'boolean',

            'calculation_method' => 'nullable|array',
            'calculation_method.*.name' => 'string',
            'calculation_method.*.slug' => 'string',
            'calculation_method.*.active' => 'bool',

            'production_days' => "array|required_if:price_build.semi_calculation,true",
            'production_days.*.day' => "string|in:mon,tue,wed,thu,fri,sat,sun",
            'production_days.*.active' => "boolean|required",
            'production_days.*.deliver_before' => "string|required",

            'production_dlv' => 'nullable|array',
            'production_dlv.*.days' => 'required|integer|min:0',
            'production_dlv.*.max_qty' => 'required|integer|min:0',
            'production_dlv.*.mode' => 'required|in:fixed,percentage',
            'production_dlv.*.value' => 'required|numeric|min:0',

            'ranges' => 'nullable|array',
            'ranges.*.name' => 'required|string',
            'ranges.*.slug' => 'required|string',
            'ranges.*.from' => 'required|numeric',
            'ranges.*.to' => 'required|numeric',
            'ranges.*.incremental_by' => 'required|numeric',

            'range_list' => 'nullable|array',

            'limits' => 'nullable|array',
            'limits.*.slug' => 'required|string',
            'limits.*.ceiling' => 'required|numeric',

            'free_entry' => 'nullable|array',
            'free_entry.*.slug' => 'string|required',
            'free_entry.*.enable' => 'boolean|required',
            'free_entry.*.interval' => 'numeric|required',

            'bleed' => 'nullable|numeric',

            'printing_method' => 'nullable|array',
            'printing_method.*.name' => 'required|string',
            'printing_method.*.slug' => 'required|string',
            'printing_method.*.from' => 'required|numeric',
            'printing_method.*.to' => 'required|numeric',


            'printing_method.*.qty_build' => 'required|array',
            'printing_method.*.qty_build.*.from' => 'required|numeric',
            'printing_method.*.qty_build.*.to' => 'required|numeric',
            'printing_method.*.qty_build.*.description' => 'required|string',
            'printing_method.*.qty_build.*.incremental_by' => 'required|numeric',
            'range_around' => 'nullable|numeric',

            'start_cost' => "required_if:price_build.semi_calculation,true|required_if:price_build.full_calculation,true",
            'start_cost.value' => 'numeric',
            'additional' => 'array',
            'additional.*.machine' => 'nullable|string',
            'vat' => 'nullable|between:0,99.99'
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure system_key is not empty
        if (empty($this->system_key) || trim($this->system_key) === '') {
            $this->merge(['system_key' => null]);
        }




        $this->merge([
            'range_around' => $this->range_around ?? 2,
            'vat' => $this->vat??0,
            'shareable' => tenant()->supplier && Context::hasMgrAddress() ? $this->shareable: false
        ]);
    }
}
