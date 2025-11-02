<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ProductCalculatePriceRequest extends FormRequest
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
            "product" => "required|array",
            "quantity" => "required|numeric",
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'quantity' => $this->quantity ?? 0
        ]);
    }
}
