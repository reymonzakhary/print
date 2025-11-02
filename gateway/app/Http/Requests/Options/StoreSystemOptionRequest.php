<?php

namespace App\Http\Requests\Options;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class StoreSystemOptionRequest extends FormRequest
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
            'sort' => 'integer|nullable',
            'name' => 'required|string',
            'checked' => 'boolean|nullable',
            'description' => 'string|nullable',
            'media' => 'array|nullable',
            'unit' => 'string|nullable',
            'minimum' => 'integer|nullable',
            'maximum' => 'integer|nullable',
            'incremental_by' => 'integer|nullable',
            'information' => 'string|nullable',
            'published' => 'boolean|nullable',
            'input_type' => 'string|nullable',
            'display_name' => 'nullable|array',
            'display_name.*.iso' => 'string',
            'display_name.*.display_name' => 'string',
            'additional.calc_ref' => 'nullable|string',
            'additional.calc_ref_type' => 'nullable|string',
        ];
    }

}
