<?php

namespace App\Http\Requests\Clients;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientRequest extends FormRequest
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
            'fqdn' => 'required|unique:hostnames|regex:/^[a-z0-9.-]+\.[a-z]{2,}$/',
            'salutation' => 'sometimes|string',
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'gender' => 'required',
            'email' => 'required|email',
            'phone' => 'required|digits_between:5,15',
            'dial_code' => 'required|exists:countries,dial_code',
            'password' => 'required|confirmed|min:6',
            'logo' => 'sometimes|file',
            'company_name' => 'required|string|max:200',
            'company_description' => 'nullable|string|max:200',
            'company_coc' => 'required_if:supplier,true|string|max:200',
            'url' => 'nullable|url',
            'role' => 'nullable',
            'namespaces' => 'nullable|array',
            'namespaces.*.area' => 'string|nullable|exists:areas,slug',
            'namespaces.*.namespace' => 'string|nullable|exists:namespaces,slug',
            'custom_fields' => 'array|nullable',
            'currency' => 'required|string',


            'contract' => 'array|required_if:supplier,true',
            'contract.payment_terms' => 'string',
            'contract.runs' => 'array',
            'contract.runs.*.from' => 'numeric',
            'contract.runs.*.to' => 'numeric',
            'contract.runs.*.percentage' => 'numeric|between:0,100',
            'contract.exchange_rate' => 'array',
            'contract.exchange_rate.*' => 'numeric',

            'manager_language' => 'nullable|string|max:2,in:EN,NL,AR,ES,DE',
            'supplier' => 'boolean',

            'address' => 'required|string',
            'number' => 'required|max:10',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'string|nullable',
            'country_id' => 'required|numeric',
            'type' => 'nullable|string|max:20',

            'tax_nr' => 'required|string|unique:companies,tax_nr',
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

            'operation_countries' => 'array|required_if:supplier,true',
            'operation_countries.*' => 'integer|exists:countries,id',

            'external_configure' => 'nullable|array'

            // NO delivery_zones validation here - handled separately
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function prepareForValidation()
    {
        if (empty($this->fqdn)) {
            throw ValidationException::withMessages(["fqdn" =>
                __("The selected fqdn is required.")
            ]);
        }

        $fqdn = Str::slug(Str::lower($this->fqdn));
        $company_name = $this->company_name ?? $this->fqdn;
        $fqdn .= '.' . env('TENANT_URL_BASE');

        $this->merge([
            'manager_language' => $this->manager_language ? Str::upper($this->manager_language) : null,
            'fqdn' => $fqdn,
            'company_name' => $company_name,
            'default' => true,
            'full_name' => Str::replace('  ', ' ', $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name),
            'team_address' => false,
            'team_id' => null,
            'team_name' => null,
            'dial_code' => (int)$this->dial_code,
            'phone' => (int)$this->phone,
        ]);
    }
}
