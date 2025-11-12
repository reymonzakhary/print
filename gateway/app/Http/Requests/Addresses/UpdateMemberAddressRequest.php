<?php

declare(strict_types=1);

namespace App\Http\Requests\Addresses;

use App\Models\Tenant\Address;
use App\Repositories\AddressRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class UpdateMemberAddressRequest extends FormRequest
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
            'number' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'region' => 'nullable|string',
            'zip_code' => 'required|string',
            'country_id' => 'required|exists:countries,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'default' => 'nullable|boolean',
            'type' => 'nullable|string|max:20',
            'company_name' => 'nullable|string',
            'full_name' => 'nullable|string',
            'tax_nr' => 'nullable|string',
            'phone_number' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:13',
            'dial_code' => 'nullable|integer',
        ];
    }


    /**
     * Set default to true user have only one address.
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {

        if (!$this->member->addresses()->where('addresses.id', $this->route('address')->id)->exists()) {
            throw ValidationException::withMessages([
                'address' => [
                    __('The giving address wasn\'t found, please try again.'),
                ]
            ]);
        }

        $this->merge([
            'default' => $this->member->addresses()->count() === 1 ?? $this->boolean('default'),
            'country_id' => $this->country_id ?? $this->route('address')->country_id,
        ]);
    }

    /**
     * @return void
     */
    protected function passedValidation(): void
    {
        $this->member->addresses()->detach($this->route('address'));

        $addressRepository = app(AddressRepository::class);
        $address = $addressRepository->firstOrCreate($this->validated());

        $addressRepository->syncWithoutDetachingToModel(
            $address,
            $this->member,
            $this->validated()
        );

        $this->address = $this->member->addresses()
            ->with('country')
            ->where('addresses.id', $address->getAttribute('id'))
            ->first();
    }
}
