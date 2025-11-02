<?php

namespace Modules\Ecommerce\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
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
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.product_type' => 'in:internal,external',
            'products.*.variations' => 'array',
            'products.*.variations.*.id' => 'exists:variations,id',
            'products.*.variations.*.quantity' => 'numeric|min:1',
            'products.*.quantity' => 'numeric|min:1'
        ];
    }
}
