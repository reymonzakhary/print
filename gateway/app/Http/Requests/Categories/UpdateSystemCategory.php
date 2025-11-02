<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class UpdateSystemCategory extends FormRequest
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
            'media' => 'nullable|array',
            'media.*.name' => 'nullable|string',
            'media.*.path' => 'nullable|string',
            'media.*.mimetype' => 'nullable|string',
            'media.*.size' => 'nullable|string',
            'description' => 'string|nullable',
            'published' => 'boolean|nullable',
            'display_name' => 'nullable|array',
            'display_name.*.iso' => 'string',
            'display_name.*.display_name' => 'string',
            'checked' => 'boolean|nullable'
        ];
    }
}
