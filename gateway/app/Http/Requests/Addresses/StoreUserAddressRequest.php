<?php

declare(strict_types=1);

namespace App\Http\Requests\Addresses;

use App\Enums\AddressType;
use App\Models\Tenants\Address;
use App\Repositories\AddressRepository;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Throwable;

final class StoreUserAddressRequest extends FormRequest
{
    public readonly Address $address;

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
            'address' => 'required|string',
            'number' => 'required|max:10',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'nullable|string',
            'country_id' => 'required|integer',
            'type' => ['nullable', 'string', 'max:20', new Enum(AddressType::class)],
            'dial_code' => 'integer|nullable',
            'is_business_user' => 'boolean|nullable',
            'company_name' => 'required_if:is_business_user,true|nullable|string',
            'phone_number' => 'required_if:is_business_user,true|nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:13' ,
            'tax_nr' => 'required_if:is_business_user,true|nullable|string',
            'full_name' => 'string',
            'default' => 'nullable|boolean',
            'team_address' => 'nullable|boolean',
            'team_id' => 'nullable|integer',
            'team_name' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ];
    }

    /**
     * Set default to true user have only one address.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'default' => !$this->user->addresses()->count() || $this->boolean('default'),
            'team_address' => false,
            'team_id' => null,
            'team_name' => null,
            ...empty($this->is_business_user) ? [
                'company_name' => null,
                'phone_number' => null,
                'tax_nr' => null,
            ] : [],
        ]);
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function passedValidation(): void
    {
        $addressRepository = app(AddressRepository::class);
        $address = $addressRepository->firstOrCreate($this->validated());
        $addressRepository->syncWithoutDetachingToModel(
            $address,
            $this->user,
            $this->validated()
        );

        $this->address = $this->user->addresses()
            ->with('country')
            ->where('addresses.id', $address->getAttribute('id'))
            ->first();
    }
}

