<?php

declare(strict_types=1);

namespace App\Http\Requests\Options\Printing;

use App\Enums\CalculationMethod;
use App\Http\Requests\MediaImageValidatorTrait;
use App\Models\Tenant\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StoreOptionRequest
 * @package App\Http\Requests\Options\Printing
 * @OA\Schema(
 * )
 */
final class StoreOptionRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", example="a4", description="required", property="name"),
     * @OA\Property(format="string", title="description", example="long text here", description="nullable|string", property="description"),
     * @OA\Property(format="string", title="linked", example="61a8f7824d1302dc3c111df7", description="nullable|string", property="linked"),
     * @OA\Property(format="boolean", title="published", example=true, description="boolean", property="published"),
     * @OA\Property(format="string", title="input_type", example="297", description="nullable|string", property="input_type"),
     * @OA\Property(format="boolean", title="has_children", example="297", description="boolean|nullable", property="has_children"),
     * @OA\Property(format="int64", title="sort", example="0", description="nullable|numeric", property="sort"),
     * @OA\Property(format="string", title="iso", example="en", description="required", property="iso"),
     * @OA\Property(format="string", title="slug", example="a4", description="nullable|string", property="slug"),
     * @OA\Property(format="int64", title="media", example="[]", description="nullable|array", property="media"),
     * @OA\Property(format="int64", title="width", example="210", description="nullable|numeric", property="width"),
     * @OA\Property(format="int64", title="height", example="297", description="nullable|numeric", property="height"),
     * @OA\Property(format="string", title="unit", example="cm", description="nullable|string", property="unit"),
     * @OA\Property(format="int64", title="maximum", example="5", description="nullable|numeric", property="maximum"),
     * @OA\Property(format="int64", title="minimum", example="200", description="nullable|numeric", property="minimum"),
     * @OA\Property(format="int64", title="incremental_by", example="5", description="nullable|numeric", property="incremental_by"),
     * @OA\Property(format="string", title="extended_fields", example="{'key':'value'}", description="nullable|json", property="extended_fields"),
     * @OA\Property(format="int64", title="parent", example="297", description="nullable|string", property="parent"),
     * @OA\Property(format="string", title="start_cost", example="50", description="nullable|numeric", property="start_cost"),
     * @OA\Property(format="string", title="calculation_method", example="[{'type':'simi','active':'true'}]", description="nullable|array", property="calculation_method"),
     * @OA\Property(format="string", title="runs", example="[{'from':'1','to':'1000','mode':'persetage','value':'200','pm':'s','active':'true'}]", description="nullable|array", property="run"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'linked' => 'nullable|string',
            'published' => 'boolean',
            'system_key' => 'required|string',
            'name' => 'required|string',
            'display_name' => 'array|nullable',
            'input_type' => 'nullable|string',
            'has_children' => 'boolean|nullable',
            'sku' => 'string|nullable',
            'sort' => 'integer|nullable',

            'media' => "nullable|array",
            'media.*' => ['string', $this->validateMediaItemsAsImages(...)],

            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'maximum_width' => 'nullable|numeric',
            'minimum_width' => 'nullable|numeric',
            'minimum_length' => 'nullable|numeric',
            'maximum_length' => 'nullable|numeric',
            'maximum_height' => 'nullable|numeric',
            'minimum_height' => 'nullable|numeric',
            'incremental_by' => 'nullable|numeric',
            'extended_fields' => 'nullable|array',
            'parent' => 'nullable|boolean',
            'dynamic' => 'nullable|boolean',
            'dynamic_keys' => 'nullable|array',
            'start_on' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $this->input('dynamic_type'),
                        ['pages', 'sides'])
                    ),
                'numeric'
            ],
            'end_on' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $this->input('dynamic_type'),
                        ['pages', 'sides'])
                    ),
                'numeric'
            ],            'generate' => 'required_if:dynamic,true|boolean',
            'dynamic_type' => 'required_if:dynamic,true|string',
            'dimension' => 'nullable|string',
            'start_cost' => 'integer',
            'calculation_method' => ['required', new Enum(CalculationMethod::class)],
            'runs' => 'nullable|array',
            'runs.*.from' => 'required|integer',
            'runs.*.to' => 'required|integer',
            'runs.*.price' => 'required|numeric',
            'runs.*.pm' => 'nullable|string',
            'runs.*.active' => 'nullable|boolean',
            'runs.*.dlv_production' => 'nullable|array',
            'runs.*.dlv_production.*.days' => 'required|integer',
            'runs.*.dlv_production.*.value' => 'nullable|integer',
            'runs.*.dlv_production.*.mode' => 'string|in:fixed,percentage',
            'sheet_runs' => 'nullable|array',
            'sheet_runs.*.from' => 'integer',
            'sheet_runs.*.to' => 'integer',
            'rpm' => 'required|integer',
            'additional' => 'nullable|array',
            'additional.calc_ref' => 'nullable|string',
            'additional.calc_ref_type' => 'nullable|string',
//            'category_id' => 'required|string',
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {

        $this->merge([
            'calculation_method' => $this->calculation_method ?? CalculationMethod::QTY->value,
            'rpm' => $this->rpm ?? 0
        ]);
    }
}
