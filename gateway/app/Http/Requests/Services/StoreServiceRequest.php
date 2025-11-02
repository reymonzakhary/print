<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreServiceRequest extends FormRequest
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
     * @param Request $request
     * @return array
     */
    public function rules(
        Request $request
    )
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|min:1|max:255',
            'price' => 'required|integer',
            'vat_id' => 'nullable|integer|exists:vats,id',
        ];
    }

}
