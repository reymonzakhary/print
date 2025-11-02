<?php

namespace Modules\Cms\Http\Requests\Snippets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSnippetRequest extends FormRequest
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
                Rule::unique('snippets')->ignore($this->chunk->id),
            ],
            'path' => 'string|nullable',
            'content' => 'string|nullable',
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
}
