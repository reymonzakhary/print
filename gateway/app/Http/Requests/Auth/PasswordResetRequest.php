<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\User;
use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
            'user' => 'required'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user' => User::where('email', $this->email)->first()
        ]);
    }
}
