<?php

namespace App\Http\Requests\Contracts;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custom_fields' => 'nullable|array',
            'custom_fields.categories' => 'array',
            'custom_fields.categories.*.id' => 'string',

            'custom_fields.discount.categories' => 'array',
            'custom_fields.discount.categories.*.id' => 'string',
            'custom_fields.discount.categories.*.mode' => 'required|string|in:price,run',
            'custom_fields.discount.categories.*.status' => 'required|boolean',
            'custom_fields.discount.categories.*.slots' => 'required|array',
            'custom_fields.discount.categories.*.slots.*.from' => 'required|integer|min:0',
            'custom_fields.discount.categories.*.slots.*.to' => 'required|integer|min:-1',
            'custom_fields.discount.categories.*.slots.*.type' => 'required|string|in:percentage,fixed',
            'custom_fields.discount.categories.*.slots.*.value' => 'required|numeric|min:0',

            'custom_fields.discount.general' => 'array',
            'custom_fields.discount.general.mode' => 'required|string|in:price,run',
            'custom_fields.discount.general.status' => 'required|boolean',
            'custom_fields.discount.general.slots' => 'nullable|array',
            'custom_fields.discount.general.slots.*.from' => 'required|integer|min:0',
            'custom_fields.discount.general.slots.*.to' => 'required|integer|min:-1',
            'custom_fields.discount.general.slots.*.type' => 'required|string|in:percentage,fixed',
            'custom_fields.discount.general.slots.*.value' => 'required|numeric|min:0',
            'start_at' => 'nullable|date|date_format:d-m-Y|after_or_equal:today',
            'period' => 'nullable|integer|min:1',
            'end_at' => 'date',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'end_at' => Carbon::parse($this->input('start_at'))->addMonths($this->input('period'))->toDateTimeString(),
        ]);

    }
}
