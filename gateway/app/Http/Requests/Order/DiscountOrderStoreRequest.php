<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DiscountOrderStoreRequest extends FormRequest
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
            'discount_id' => 'nullable|exists:discounts,id|integer',
        ];
    }

    public function prepareForValidation()
    {
        if (!auth()->user()->can('orders-discount-create')) {
            throw ValidationException::withMessages([
                'orders_discount' => __('Not permitted action.')
            ]);
        }
    }
}
