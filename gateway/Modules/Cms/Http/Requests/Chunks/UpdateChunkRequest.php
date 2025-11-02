<?php

namespace Modules\Cms\Http\Requests\Chunks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateChunkRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                Rule::unique('chunks')->ignore($this->chunk->id),
            ],
            'folder_id' => 'integer|nullable|exists:folders,id',
            'path' => 'string|nullable',
            'content' => 'file|nullable',
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
        if ($this->name) {
            $this->merge(['name' => Str::camel(trim(str_replace([' ', '-', '_'], ['', '', ''], $this->name)))]);
        }
    }
}
