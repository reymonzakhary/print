<?php

namespace App\Http\Requests\Variations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateVariationRequest extends FormRequest
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
    public function rules(
        Request $request
    )
    {
        return [
            'category_id' => 'required|string|exists:assortments,id',
            'box_id' => 'required|integer|max:255|exists:boxes,id',
            'option_id' => 'required|integer|max:255|exists:options,id',
            'price' => 'sometimes|integer',
            'incremental' => 'sometimes|integer',
            'sort' => 'sometimes|integer',
            'published' => 'sometimes|integer',
            'override' => 'sometimes|integer',
        ];
    }
}
