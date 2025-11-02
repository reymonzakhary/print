<?php

namespace App\Http\Requests\Boxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreBoxRequest extends FormRequest
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
            'name' => 'required|string|unique:boxes',
        ];
    }

}
