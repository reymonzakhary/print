<?php

declare(strict_types=1);

namespace App\Http\Requests\Addresses;

use App\Facades\Context;
use App\Models\Tenants\Address;
use App\Repositories\AddressRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class StoreContextAddressRequest extends FormRequest
{
    public readonly Address $address;


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
     * @OA\Property(format="string", title="address", example="3 wall st - newyork", description="required|string", property="address"),
     * @OA\Property(format="string", title="number", example="12", description="required", property="number"),
     * @OA\Property(format="string", title="city",example="usa", description="required|string", property="city"),
     * @OA\Property(format="string", title="zip_code", example="123", description="required|string", property="zip_code"),
     * @OA\Property(format="string", title="region", default="null",example="newyork", description="string|nullable", property="region"),
     * @OA\Property(format="string", title="country_id", example="1", description="required|integer", property="country_id"),
     * @OA\Property(format="string", title="type", example="work", description="string|nullable", property="type"),
     * @OA\Property(format="string", title="company_name", example="CHD", description="string|nullable", property="company_name"),
     * @OA\Property(format="string", title="phone_number", example="01125902552", description="regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:13", property="phone_number"),
     * @OA\Property(format="string", title="tax_nr", example="123", description="string", property="tax_nr"),
     * @OA\Property(format="string", title="full_name", example="Ahmed hifny", description="string", property="full_name"),
     * @OA\Property(format="string", title="default", example="false", description="boolean", property="default"),
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'address' => 'required|string',
            'number' => 'required|max:10',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'string|nullable',
            'country_id' => 'required|integer',
            'type' => 'nullable|string|max:20',
            'is_business_user' => 'boolean|nullable',
            'company_name' => 'required_if:is_business_user,true|nullable|string',
            'phone_number' => 'required_if:is_business_user,true|nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:13' ,
            'tax_nr' => 'required_if:is_business_user,true|nullable|string',
            'full_name' => 'string',
            'default' => 'boolean',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        $addressRepository = app(AddressRepository::class);
        $address = $addressRepository->firstOrCreate($this->validated());

        $addressRepository->syncWithoutDetachingToModel(
            $address,
            $this->context,
            $this->validated()
        );

        $this->address = $this->context->addresses()
            ->with('country')
            ->where('addresses.id', $address->getAttribute('id'))
            ->first();
        Context::refresh();
    }
}
