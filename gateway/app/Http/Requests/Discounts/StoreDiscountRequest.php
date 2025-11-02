<?php

namespace App\Http\Requests\Discounts;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {

        return [
            'type' => 'required|string|in:fixed,percentage',
            'value' => 'required|integer',
            'ctx_id' => 'required|integer|exists:contexts,id',
        ];
    }

}
