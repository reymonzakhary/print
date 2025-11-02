<?php

namespace Modules\Ecommerce\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'reference' => 'string|max:100',
            'discount_id' => 'nullable|exists:discounts,id',
            'type' => 'boolean|nullable',
            'delivery_multiple' => 'boolean',
            'delivery_pickup' => 'boolean',
            'payment_method' => 'nullable',
            'shipping_cost' => 'nullable|integer|min:1',
            'note' => 'string|min:3|max:255',
            'ctx_id' => 'integer|exists:contexts,id',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
            'ctx_id' => 2,
            'type' => 1
        ]);
    }
}
