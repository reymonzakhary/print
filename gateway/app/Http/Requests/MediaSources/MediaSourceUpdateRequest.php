<?php

namespace App\Http\Requests\MediaSources;

use Illuminate\Foundation\Http\FormRequest;

class MediaSourceUpdateRequest extends FormRequest
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
                'required',
                'max:255',
                "unique:media_sources,name,{$this->media_source->id}"
            ],
            'ctx_id' => [
                'required',
                'exists:contexts,id',
            ]
        ];
    }
}
