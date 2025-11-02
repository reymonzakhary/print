<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ImpersonateLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * In most cases, this will be accessible publicly via a signed URL or secure token.
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
            'token' => 'required'
        ];
    }

    /**
     * Custom messages for validation errors (optional).
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token.required' => __('The impersonation token is required.'),
        ];
    }
}
