<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTenantHostnameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|min:3|max:50',
            'email' => 'sometimes|string|email',
            'gender' => 'sometimes|string|in:male,female,other,',
            'tax_nr' => 'sometimes|string|min:3|max:50',
            'company_name' => 'sometimes|string|max:50',
            'coc' => 'sometimes|string|min:3|max:50',
            'logo' => 'sometimes|string|max:500',
            'page_title' => 'nullable|string|max:255',
            'page_description' => 'nullable|string|max:500',
            'page_media' => 'nullable|array',
            'shared_categories' => 'nullable|array',
            'currency' => 'nullable|string',
            'manager_language' => 'nullable|string',
            'operation_countries' => 'array|required_if:supplier,true',
            'operation_countries.*' => 'integer',
            'address' => 'required|array',
            'address.lat' => 'required|numeric',
            'address.lng' => 'required|numeric',
            'address.format_address' => 'nullable|string',
            'address.floor' => 'nullable|numeric',
            'address.apartment' => 'nullable|numeric',
            'address.neighborhood' => 'nullable|string',
            'address.landmark' => 'nullable|string',
            'address.address' => 'required|string',
            'address.number' => 'required|max:10',
            'address.city' => 'required|string',
            'address.zip_code' => 'required|string',
            'address.region' => 'required|nullable',
            'address.country_id' => 'required|integer',
        ];
    }
}
