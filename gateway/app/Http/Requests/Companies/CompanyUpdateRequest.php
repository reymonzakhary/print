<?php

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string|max:255',
            'email' => 'email|max:255|unique:companies,email,'.$this->company->id,
            'coc' => 'string|nullable',
            'tax_nr' => 'string|nullable',
            'url' => 'active_url|url|nullable',
            'vat_id' => 'string|digits_between:1,100|nullable',
            'dial_code' => 'integer|nullable',
            'phone' => 'integer|nullable',
        ];
    }
}
