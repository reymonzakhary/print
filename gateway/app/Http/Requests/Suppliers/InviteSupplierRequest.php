<?php

namespace App\Http\Requests\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class InviteSupplierRequest extends FormRequest
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
            'gender' => 'required|in:male,female,other',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'company' => 'required|string|min:3',
            'domain' => ['required', 'regex:/^(?!(www|http|https)\.)(.*?)(\.\w+)+$/'],
            'coc' => 'required|string|min:3',
            'tax_nr' => 'nullable|string|min:3',
            'fqdn' => 'required',
            'primary' => 'boolean',
            'password' => 'required',
            'role' => 'required|string',
            'namespaces' => 'required|array',
            'namespaces.*.area' => 'string',
            'namespaces.*.namespace' => 'string',
            'manager_language' => 'nullable|string|max:2,in:EN,NL,AR,ES,DE'
        ];
    }

    /**
     *
     */
    public function prepareForValidation()
    {
        $this->merge([
            'manager_language' => $this->manager_language?Str::upper($this->manager_language):null,
            'primary' => true,
            'fqdn' => Str::lower('pr-' . Str::random(4)) . '.' . env('TENANT_URL_BASE'),
            'domain' => preg_replace('/https|http|\/\/|\/|:|www\.|/', '$1', $this->domain),
            'password' => Str::random(10),
            'role' => 'quotation_supplier',
            'namespaces' => [[
                "area" => "quotation",
                "namespace" => "orders"
            ]]

        ]);
    }
}
