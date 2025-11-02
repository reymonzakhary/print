<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

final class StoreBoopsRequest extends FormRequest
{
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
    public function rules(): array
    {
        return [
            'iso' => 'required',
            'slug' => 'required',
            'name' => 'required',
            'display_name' => 'nullable',
            'published' => 'boolean',
            'shareable' => 'boolean',
            'boops' => 'required|array',
            "boops.*.input_type" => "nullable",
            "boops.*.name" => "required",
            "boops.*.display_name" => "nullable",
            "boops.*.slug" => "required",
            "boops.*.unit" => "nullable",
            "boops.*.maximum" => "nullable",
            "boops.*.minimum" => "nullable",
            "boops.*.incremental_by" => "nullable",
            "boops.*.ops" => "required|array",
            "boops.*.ops.*.name" => "required",
            "boops.*.ops.*.display_name" => "nullable",
            "boops.*.ops.*.system_key" => "required",
            "boops.*.ops.*.slug" => "required",
            "boops.*.ops.*.excludes" => "array",
            "boops.*.ops.*.dynamic" => "nullable",
            "boops.*.ops.*.dynamic_keys" => "nullable|array",
            "boops.*.ops.*.start_on" => "required_if:dynamic,true",
            "boops.*.ops.*.end_on" => "required_if:dynamic,true",
            "boops.*.ops.*.generate" => "required_if:dynamic,true",
            "boops.*.ops.*.dynamic_type" => "required_if:dynamic,true",
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            [
                'iso' => App::getLocale(),
                'published' => false,
                'shareable' => false,
            ]
        );
    }


    /**
     * Get custom error messages for validator rules.
     *
     * This method defines custom validation messages for specific rules
     * used in the request. If a rule is not listed here, Laravel will use
     * the default message from the validation language files.
     *
     * @return array<string, string> An array of rule keys mapped to custom messages.
     */
    public function messages(): array
    {
        return [
            'iso.required' => __('The language (ISO) is required.'),
            'slug.required' => __('The category slug is required.'),
            'name.required' => __('The category name is required.'),
            'published.boolean' => __('The published field must be true or false.'),
            'shareable.boolean' => __('The shareable field must be true or false.'),

            'boops.required' => __('The Box of Options is required.'),
            'boops.array' => __('The Box of Options must be an array.'),

            'boops.*.name.required' => __('Each option in the Box must have a name.'),
            'boops.*.slug.required' => __('Each option in the Box must have a slug.'),

            'boops.*.ops.required' => __('Each Box of Options (Boops) must contain at least one Option.'),
            'boops.*.ops.array' => __('The Options inside the Box must be an array.'),

            'boops.*.ops.*.name.required' => __('Each Option must have a name.'),
            'boops.*.ops.*.system_key.required' => __('Each Option must have a system key.'),
            'boops.*.ops.*.slug.required' => __('Each Option must have a slug.'),
            'boops.*.ops.*.excludes.array' => __('The excludes field must be an array if present.'),
            'boops.*.ops.*.dynamic_keys.array' => __('Dynamic keys must be an array.'),

            'boops.*.ops.*.start_on.required_if' => __('The start date is required when the Option is dynamic.'),
            'boops.*.ops.*.end_on.required_if' => __('The end date is required when the Option is dynamic.'),
            'boops.*.ops.*.generate.required_if' => __('The generate field is required when the Option is dynamic.'),
            'boops.*.ops.*.dynamic_type.required_if' => __('The dynamic type is required when the Option is dynamic.'),
        ];
    }

}
