<?php

namespace App\Http\Requests\Options;

use Illuminate\Foundation\Http\FormRequest;

class AttachOptionRequest extends FormRequest
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
            'type' => 'required|in:suppliers,unmatched,matches|string',
            'tenant_id' => 'required|string',
            'slug' => 'required|string'
        ];
    }
}
