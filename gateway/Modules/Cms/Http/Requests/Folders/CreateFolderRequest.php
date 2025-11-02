<?php

namespace Modules\Cms\Http\Requests\Folders;

use Illuminate\Foundation\Http\FormRequest;

class CreateFolderRequest extends FormRequest
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
            'name' => 'required',
            'description' => 'string|max:255',
            'parent_id' => 'nullable|integer|exists:folders,id',
            'sort' => 'integer|nullable',
        ];
    }

}
