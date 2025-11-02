<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'name' => 'required|string|unique:services,id,' . $this->service,
            'description' => 'required|string|max:255',
            'file' => 'sometimes|integer|max:255|exists:media,id',
            'price' => 'required|integer',
            'discount_id' => 'required|integer|exists:discounts,id',
            'vat_id' => 'required|integer|exists:vats,id',
        ];
    }
}
