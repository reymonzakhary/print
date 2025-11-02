<?php

namespace App\Http\Requests\Boxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class StoreSystemBoxRequest extends FormRequest
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
            "name" => "required|string",
            'description' => 'string|nullable',
            'media' => 'array|nullable',
            'published' => 'boolean|nullable',
            'input_type' => 'string|nullable',
            'sqm' => 'boolean|nullable',
            'appendage' => 'boolean',
            'checked' => 'boolean|nullable',
            'display_name' => 'nullable|array',
            'display_name.*.iso' => 'string',
            'display_name.*.display_name' => 'string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'appendage' => $this->appendage?? false,
            'sqm' => $this->sqm?? false,
            'input_type' => $this->input_type??'',
            'published' => true,
            'media' => $this->media?? [],
            'description' => $this->description?? '',
            'sort' => $this->sort?? 1,
        ]);
    }
}
