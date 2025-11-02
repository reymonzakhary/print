<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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

            'type' => 'boolean|nullable',
            'ctx_id' => 'integer|exists:contexts,id',
            'delivery_pickup' => [
                'boolean'
            ],
//            'delivery_separated' => [
//                'required_if:delivery_pickup,false',
//                function($attribute, $value, $fail) {
//                    return request()->replace([$attribute => NULL]);
//                }
//            ],
            'reference' => 'string|min:1|max:255',
            'addresses' => [
                'array',
                'required_if:delivery_separated,true',
            ],
            'address' => [
                'required_if:delivery_separated,false',
                'integer',
                'exists:addresses,id',

            ],
            'payment_method' => 'nullable',
            'created_from' => 'required|in:mgr,api,web',
            'address_type' => 'nullable|string',
            'address_full_name' => 'nullable|string',
            'address_company_name' => 'nullable|string',
            'address_phone_number' => 'nullable|string',
            'address_tax_nr' => 'nullable|string',
            'team_id' => 'nullable|exists:teams,id'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'ctx_id' => auth()->user()->contexts()->where('name', 'mgr')->first()->id,
            'type' => true,
            'delivery_pickup' => $this->delivery_pickup ?? false,
            'delivery_separated' => $this->delivery_separated ?? false,
            'created_from' => $this->created_from ?? 'mgr',
            'address_type' => 'order',
            'address_full_name' => $this->address_full_name ?? auth()->user()->profile->first_name . ' ' . auth()->user()->profile->last_name,
            'address_company_name' => $this->address_full_name ?? null,
            'address_phone_number' => $this->address_phone_number ?? null,
            'address_tax_nr' => $this->address_tax_nr ?? null,
            'address_team_address' => $this->address_team_address ?? null,
            'team_id' => $this->team_id ?? null,
            'team_name' => $this->team_name ?? null,
        ]);
    }
}
