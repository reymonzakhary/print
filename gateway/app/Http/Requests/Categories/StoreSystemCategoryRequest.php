<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class StoreSystemCategoryRequest extends FormRequest
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

            'name' => 'required|min:2',
            'iso' => 'required',
            'media' => 'nullable',
            'description' => 'string|nullable',
            'published' => 'boolean|nullable'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['iso' => App::getLocale()]);
    }
}
