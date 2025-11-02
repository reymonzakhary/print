<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryMarginRequest extends FormRequest
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
     */
    public function rules()
    {
        return [
            "supplier_id" => 'required|string',
            'margin' => 'required|array',
            'margin.*.mode' => 'required|string|in:price,run',
            'margin.*.status' => 'required|boolean',
            'margin.*.slots' => 'nullable|array',
            'margin.*.slots.*.from' => 'required|integer|min:0',
            'margin.*.slots.*.to' => 'required|integer|min:-1',
            'margin.*.slots.*.type' => 'required|string|in:percentage,fixed',
            'margin.*.slots.*.value' => 'required|numeric|min:0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(
            [
                'supplier_id' => $this->supplier_id ?? tenant()->uuid,
            ]
        );
    }
}
