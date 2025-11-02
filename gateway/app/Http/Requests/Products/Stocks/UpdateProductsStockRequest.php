<?php

namespace App\Http\Requests\Products\Stocks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductsStockRequest extends FormRequest
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
            'location_id' => "required|integer|exists:locations,id",
            'qty' => 'required|integer'
        ];
    }
}
