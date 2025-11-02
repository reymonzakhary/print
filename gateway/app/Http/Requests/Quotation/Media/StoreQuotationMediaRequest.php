<?php

namespace App\Http\Requests\Quotation\Media;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreQuotationMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array',
            'overwrite' => 'required|boolean'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'overwrite' => true
        ]);
    }
}
