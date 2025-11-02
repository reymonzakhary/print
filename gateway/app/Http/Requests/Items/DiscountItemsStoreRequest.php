<?php

namespace App\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;

class DiscountItemsStoreRequest extends FormRequest
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
}
