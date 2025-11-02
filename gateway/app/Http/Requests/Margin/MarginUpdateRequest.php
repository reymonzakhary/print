<?php

namespace App\Http\Requests\Margin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MarginUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'general' => 'array|required',
            'general.*.mode' => 'required|string|in:price,run',
            'general.*.slots' => 'nullable|array',
            'general.*.slots.*.from' => 'required|integer|min:0',
            'general.*.slots.*.to' => 'required|integer|min:-1',
            'general.*.slots.*.type' => 'required|string|in:percentage,fixed',
            'general.*.slots.*.value' => 'required|numeric|min:0',
            'general.*.status' => 'required|boolean',
        ];
    }
}
