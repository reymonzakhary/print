<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class PasswordResetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email'
        ];
    }

    /**
     * @return void
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'user' => User::query()->where('email', $this->get('email'))->firstOrFail()
        ]);
    }
}
