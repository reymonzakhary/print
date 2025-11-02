<?php

namespace Modules\Cms\Http\Requests\Variables;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariableRequest extends FormRequest
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
            'label' => 'required|string|max:255',
            'key' => 'string|max:255|unique:variables,key,' . $this->variable->id,
            'folder_id' => 'nullable|integer|exists:folders,id',
            'data_type' => 'string|nullable',
            'input_type' => 'string|nullable',
            'defualt_value' => 'string|nullable',
            'placeholder' => 'string|max:255|nullable',
            'class' => 'string|nullable',
            'secure_variable' => 'boolean|nullable',
            'multi_select' => 'boolean|nullable',
            'incremental' => 'boolean|nullable',
            'min_count' => 'integer|nullable',
            'max_count' => 'integer|nullable',
            'min_size' => 'integer|nullable',
            'max_size' => 'integer|nullable',
        ];
    }


}
