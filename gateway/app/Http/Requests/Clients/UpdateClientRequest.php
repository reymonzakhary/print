<?php

namespace App\Http\Requests\Clients;

use App\Models\Country;
use App\Models\Tenants\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateClientRequest extends FormRequest
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
            'fqdn' => [
                'required',
                Rule::unique('hostnames')->ignore($this->tenant),
                'alpha_dash:ascii',
            ],
            'salutation' => 'sometimes|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'tax_nr' => 'required|string|unique:companies,tax_nr,' . $this->tenant?->id,
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'dial_code' => 'required|exists:countries,dial_code',
            'company_name' => 'required|string|max:200',
            'company_description' => 'nullable|string|max:200',
            'company_coc' => 'required_if:supplier,true|string|max:200',
            'domain' => 'nullable|string|max:200',
            'logo' => 'sometimes|file',
            'supplier' => 'sometimes|boolean',
            'url' => 'nullable|url',
            'address' => 'required|string',
            'number' => 'required|max:10',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'required|nullable',
            'country_id' => 'required|integer',
            'type' => 'string|max:20',
            'full_name' => 'string',
            'default' => 'nullable|boolean',
            'team_address' => 'nullable|boolean',
            'team_id' => 'nullable|integer',
            'team_name' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'format_address' => 'nullable|string',
            'floor' => 'nullable|numeric',
            'apartment' => 'nullable|numeric',
            'neighborhood' => 'nullable|string',
            'landmark' => 'nullable|string',
            'currency' => 'required|string',


            'contract' => 'array|required_if:supplier,true',
            'contract.payment_terms' => 'string',
            'contract.runs' => 'array',
            'contract.runs.*.from' => 'numeric',
            'contract.runs.*.to' => 'numeric',
            'contract.runs.*.percentage' => 'numeric|between:0,100',
            'contract.exchange_rate' => 'array',
            'contract.exchange_rate.*' => 'numeric',

            'operation_countries' => 'array|required_if:supplier,true',
            'operation_countries.*' => 'integer|exists:countries,id',

            'external_configure' => 'nullable|array'


        ];
    }


    /**
     * @throws ValidationException
     */
    public function prepareForValidation()
    {
        $this->merge([
            'manager_language' => $this->manager_language ? Str::upper($this->manager_language) : null,
        ]);
        switchTenant($this->tenant->website->uuid);
        $user = User::query()->owner()->first();
        if (User::query()->where('email' , $this->get('email'))->whereNot('id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('Email already exists.'),
            ]);
        }
    }

    /**
     * @return void
     */
    public function passedValidation(): void
    {
        if ($this->exists('first_name') || $this->exists('last_name')) {
            $this->merge([
                'name' => sprintf(
                    '%s %s',
                    $this->input('first_name'),
                    $this->input('last_name')
                ),
                'default' => true,
                'full_name' => Str::replace('  ', ' ', $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name),
                'team_address' => false,
                'team_id' => null,
                'team_name' => null,
                'country_id' => Country::query()->where('iso2', Str::upper($this->country))->first()?->id,
                'dial_code' => (int)$this->dial_code,
                'phone' => (int)$this->phone,
            ]);
        }
    }
}
