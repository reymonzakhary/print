<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class TeamUpdateRequest extends FormRequest
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
                'unique:teams,name,' . $this->team?->id
            ],
            'description' => 'max:255',
            'users' => 'nullable|array',
            'users.*' => 'integer|exists:users,id',
            'address' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:addresses,id',
            ],
            'address_type' => 'nullable|string',
            'address_full_name' => 'nullable|string',
            'address_company_name' => 'nullable|string',
            'address_phone_number' => 'nullable|string',
            'address_tax_nr' => 'nullable|string',
            'address_team_address' => 'nullable|boolean',
            'address_team_id' => 'nullable|integer',
            'address_team_name' => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string booleans to actual booleans if needed
        if ($this->has('address_team_address')) {
            $this->merge([
                'address_team_address' => filter_var($this->address_team_address, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
