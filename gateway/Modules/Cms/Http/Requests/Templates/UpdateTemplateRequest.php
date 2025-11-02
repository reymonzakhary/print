<?php

namespace Modules\Cms\Http\Requests\Templates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends FormRequest
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
            'name' => [
                Rule::unique('templates')->ignore($this->template->id),
            ],
            'description' => 'string|max:255|nullable',
            'icon' => 'string|max:255|nullable',
            'type' => 'string|nullable',
            'locked' => 'boolean|nullable',
            'folder_id' => 'nullable|integer|exists:folders,id',
            'properties' => 'json|nullable',
            'content' => 'file',
            'static' => 'boolean|nullable',
            'path' => 'string|nullable|max:255',
            'sort' => 'integer|nullable',
        ];
    }


}
