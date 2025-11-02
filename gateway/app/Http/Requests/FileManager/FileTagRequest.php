<?php

namespace App\Http\Requests\FileManager;

use Illuminate\Foundation\Http\FormRequest;

class FileTagRequest extends FormRequest
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
            "files" => 'required|array',
            'files.*.path' => 'required|string',
            'files.*.tags' => 'array',
            'files.*.tags.*' => 'required|exists:tags,id'
        ];
    }
}
