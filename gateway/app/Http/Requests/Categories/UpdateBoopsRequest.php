<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

final class UpdateBoopsRequest extends FormRequest
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
            'id' => 'nullable',
            'ref_id' => 'nullable',
            'ref_boops_name' => 'nullable',
            'ref_boops_id' => 'nullable',
            'published' => 'boolean',
            'shareable' => 'boolean',
            'divided' => 'nullable|boolean',
            'boops' => 'required|array',
            "boops.*.input_type" => "nullable",
            "boops.*.id" => "required|string",
            "boops.*.name" => "required",
            "boops.*.display_name" => "nullable",
            "boops.*.slug" => "required",
            "boops.*.unit" => "nullable",
            "boops.*.linked" => "nullable|string",
            "boops.*.divider" => "required_if:divided,true",
            "boops.*.maximum" => "nullable",
            "boops.*.minimum" => "nullable",
            "boops.*.incremental_by" => "nullable",
            "boops.*.ref_box" => "nullable",
            "boops.*.ops" => "required|array",
            "boops.*.ops.*.id" => "required|string",
            "boops.*.ops.*.name" => "required",
            "boops.*.ops.*.display_name" => "nullable",
            "boops.*.ops.*.system_key" => "nullable",
            "boops.*.ops.*.linked" => "nullable|string",
            "boops.*.ops.*.slug" => "required",
            "boops.*.ops.*.excludes" => "array",
            "boops.*.ops.*.ref_option" => "nullable",
            "boops.*.ops.*.dynamic" => "nullable",
            "boops.*.ops.*.dynamic_keys" => "nullable|array",
            "boops.*.ops.*.start_on" => "required_if:dynamic,true",
            "boops.*.ops.*.end_on" => "required_if:dynamic,true",
            "boops.*.ops.*.generate" => "required_if:dynamic,true",
            "boops.*.ops.*.dynamic_type" => "required_if:dynamic,true",
        ];
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            [
                'iso' => App::getLocale(),
                'published' => false,
                'shareable' => false,
                'divided' => $this->has('divided') ? $this->get('divided') : false
            ]
        );
    }

    /**
     * Perform actions after validation has passed.
     *
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        foreach ($this->get('boops') as $boxIndex => $boxData) {
            $processedOptionsSystemKeys = [];

            foreach ($boxData['ops'] as $boxOptionData) {
                $boxOptionSystemKey = optional($boxOptionData)['slug'];

                if (in_array($boxOptionSystemKey, $processedOptionsSystemKeys, true)) {
                    throw ValidationException::withMessages([
                        sprintf('boops.%s.ops', $boxIndex) => sprintf(
                            'Option "%s" already exists in the Box "%s".',
                            $boxOptionData['name'],
                            $boxData['name']
                        )
                    ]);
                }

                $processedOptionsSystemKeys[] = $boxOptionSystemKey;
            }
        }
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'boops.required' => __('You must provide at least one Box of Options (boop).'),

            'boops.*.name.required' => __('Each Box of Options (boop) must have a name.'),
            'boops.*.slug.required' => __('Each Box of Options (boop) must have a slug.'),
            'boops.*.divider.required_if' => __('Divider is required when the Box of Options (boop) is divided.'),

            'boops.*.ops.required' => __('Each Box of Options (boop) must include at least one option.'),
            'boops.*.ops.array' => __('The options for each Box of Options (boop) must be an array.'),

            'boops.*.ops.*.name.required' => __('Each option must have a name.'),
            'boops.*.ops.*.slug.required' => __('Each option must have a slug.'),

            'boops.*.ops.*.start_on.required_if' => __('Start date is required when the option is dynamic.'),
            'boops.*.ops.*.end_on.required_if' => __('End date is required when the option is dynamic.'),
            'boops.*.ops.*.generate.required_if' => __('Generate is required when the option is dynamic.'),
            'boops.*.ops.*.dynamic_type.required_if' => __('Dynamic type is required when the option is dynamic.'),

            'boops.*.ops' => __('Each Box of Options (boop) must contain unique options. Duplicates are not allowed.'),
        ];
    }

}
