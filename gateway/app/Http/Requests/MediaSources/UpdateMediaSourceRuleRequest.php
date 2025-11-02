<?php

namespace App\Http\Requests\MediaSources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class UpdateMediaSourceRuleRequest extends FormRequest
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
            'user_id' => 'required_without:media_source_id|exists:users',
            'media_source_id' => 'required_without:user_id|exists:mediaSources',
            'disk' => 'required|max:255',
            'path' => [
                "required",
                "string",
                function ($attribute, $value, $fail) {
                    if (!Storage::exists($value)) {
                        $fail(_('The path ' . $attribute . ' dose not exists.'));
                    }
                },
            ],
            'access' => 'required|integer|in:0,1,2',
        ];
    }
}
