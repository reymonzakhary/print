<?php

declare(strict_types=1);

namespace App\Http\Requests\Boxes\Printing;

use App\Enums\BoxCalcRefs;
use App\Http\Requests\MediaImageValidatorTrait;
use App\Models\Tenants\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateBoxRequest
 * @package App\Http\Requests\Boxes\Printing
 * @OA\Schema(
 * )
 */
final class UpdateBoxRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", example="format", description="required", property="name"),
     * @OA\Property(format="string", title="description", example="long text here", description="nullable|string", property="description"),
     * @OA\Property(format="string", title="linked", example="61a8f7824d1302dc3c111df7", description="nullable|string", property="linked"),
     * @OA\Property(format="int64", title="input_type", example="radio", description="required|in:text,radio,checkbox,number", property="input_type"),
     * @OA\Property(format="boolean", title="incremental", example="true", description="boolean", property="incremental"),
     * @OA\Property(format="int64", title="select_limit", example="2", description="integer", property="select_limit"),
     * @OA\Property(format="int64", title="option_limit", example="2", description="integer", property="option_limit"),
     * @OA\Property(format="boolean", title="sqm", example="297", description="boolean", property="sqm"),
     * @OA\Property(format="boolean", title="published", example=true, description="boolean", property="published"),
     * @OA\Property(format="string", title="iso", example="en", description="required", property="iso"),
     * @OA\Property(format="int64", title="start_cost", example="2", description="nullable|integer", property="start_cost"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'input_type' => 'nullable|in:text,radio,checkbox,number',

            'display_name' => 'nullable|array',
            'display_name.*.iso' => 'required|string',
            'display_name.*.display_name' => 'required|string',

            'system_key' => 'required|string',
            'incremental' => 'boolean',
            'select_limit' => 'integer',
            'option_limit' => 'integer',
            'sqm' => 'boolean',

            'media' => "nullable|array",
            'media.*' => ['string', $this->validateMediaItemsAsImages(...)],

            'published' => 'boolean',
            'appendage' => 'boolean',
            'start_cost' => 'nullable|integer',
            'calc_ref' => ['required', new Enum(BoxCalcRefs::class)],
            'additional' => 'array',
            'additional.excludes_visible' => 'boolean',

            'id' => 'required|string',
            'category_id' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'calc_ref.required' => __('the calc ref field is required'),
            'calc_ref.in' => __('the calc ref field must be in ' . BoxCalcRefs::all()->join(', ')),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->display_name)) {
            $display_name = [];
            foreach (Language::pluck('iso') as $lang) {
                $display_name[] = [
                    'display_name' => $this->display_name,
                    'iso' => $lang
                ];
            }
        }

        $this->merge([
            'display_name' => is_string($this->display_name) ? $display_name : $this->display_name,
            "additional" => $this->additional? $this->additional : [
                "excludes_visible" => true
            ],
        ]);
    }
}
