<?php

declare(strict_types=1);

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;

final class SendQuotationMailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'language' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'greeting' => 'nullable|string',
            'regards' => 'nullable|string',
        ];
    }
}
