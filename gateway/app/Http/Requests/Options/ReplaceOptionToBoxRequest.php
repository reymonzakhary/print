<?php

namespace App\Http\Requests\Options;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceOptionToBoxRequest extends FormRequest
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
            'slug' => 'string|required'
        ];
    }
}
