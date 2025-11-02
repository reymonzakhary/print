<?php

namespace Modules\Cms\Http\Requests\Chunks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreChunkRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:chunks,name',
            'path' => 'string|nullable',
            'content' => 'string|nullable',
            'folder_id' => 'integer|nullable|exists:folders,id',
            'sort' => 'integer|nullable'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge(['name' => Str::camel(trim(str_replace([' ', '-', '_'], ['', '', ''], $this->name)))]);
    }
}
