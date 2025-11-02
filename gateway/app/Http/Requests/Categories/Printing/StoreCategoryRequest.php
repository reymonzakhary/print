<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories\Printing;

use App\Http\Requests\MediaImageValidatorTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Class StoreCategoryRequest
 * @package App\Http\Resources\Categories
 * @OA\Schema(
 *     schema="StoreCategoryRequest",
 *     title="Print Category Request"
 *
 * )
 */
final class StoreCategoryRequest extends FormRequest
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
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="int64", title="linked", description="linked", property="linked" ,example="null | 61b1b520a50498808633c525"),
     * @OA\Property(format="string", title="iso", default="", description="iso", example="EN", property="iso"),
     * @OA\Property(format="string", title="sort", default="0", description="sort", property="sort"),
     * @OA\Property(format="string", title="name", description="name", property="name", example="Posters"),
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
    /**
     * @OA\Schema(
     *  schema="countriesArea",
     *  title="countries",
     * 	@OA\Property(property="currency",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="code", type="string", example="&euro;"),
     *          @OA\Property(property="name", type="string", example="aeuro"),
     *          @OA\Property(property="symbol", type="string", example="€")
     *        )
     *     ),
     * 	@OA\Property(property="iso_2",type="string", example="nl"),
     * 	@OA\Property(property="iso_3",type="string", example="nld"),
     * 	@OA\Property(property="name",type="string", example="Netherlands"),
     * 	@OA\Property(property="slug",type="string", example="netherlands"),
     * )
     */
    public function rules(): array
    {
        return [
            'sort' => 'nullable|integer',
            'published' => 'required|bool',
            'linked' => 'nullable|string',

            'countries' => 'array',
            'countries.*.name' => 'string',
            'countries.*.iso2' => 'string',
            'countries.*.iso3' => 'string',
            'countries.*.un_code' => 'string',
            'countries.*.currency' => 'array',
            'countries.*.currency.name' => 'string',
            'countries.*.currency.symbol' => 'string',
            'countries.*.currency.iso' => 'string',

            'name' => 'required|string',
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

            'dlv_days' => "required_if:price_build.semi_calculation,true",
            'dlv_days.*.label' => 'required|string',
            'dlv_days.*.days' => 'required|numeric',
            'dlv_days.*.value' => 'required|numeric',
            'dlv_days.*.mode' => 'required|string',

            'printing_method' => "required_if:price_build.semi_calculation,true",
            'printing_method.*.name' => 'required|string',
            'printing_method.*.slug' => 'required|string',
            'printing_method.*.from' => 'required|numeric',
            'printing_method.*.to' => 'required|numeric',

            'printing_method.*.dlv_days' => 'required|array',
            'printing_method.*.dlv_days.*.availability' => 'required|array',
            'printing_method.*.dlv_days.*.availability.from' => 'required|numeric',
            'printing_method.*.dlv_days.*.availability.to' => 'required|numeric',
            'printing_method.*.dlv_days.*.days' => 'required|numeric',

//            'printing_method.*.qty_build' =>'required|array',
//            'printing_method.*.qty_build.*.from' =>'required|numeric',
//            'printing_method.*.qty_build.*.to' =>'required|numeric',
//            'printing_method.*.qty_build.*.price' =>'required|array',
//            'printing_method.*.qty_build.*.price.value' =>'required|numeric',
//            'printing_method.*.qty_build.*.price.mode' =>'required|string',
//            'printing_method.*.qty_build.*.price.on' =>'required|string',
//            'printing_method.*.qty_build.*.description' =>'required|string',
//            'printing_method.*.qty_build.*.incremental_by' =>'required|numeric',

            'start_cost' => "required_if:price_build.semi_calculation,true|required_if:price_build.full_calculation,true",
            'start_cost.value' => 'numeric',
            'additional' => 'array',
            'additional.*.machine' => 'nullable|string',
            'vat' => 'nullable|between:0,99.99',
            'display_name' => 'required|array',
            'display_name.*.iso' => 'required|string',
            'display_name.*.display_name' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'system_key' => Str::slug($this->input('name')),
        ]);
    }

}
