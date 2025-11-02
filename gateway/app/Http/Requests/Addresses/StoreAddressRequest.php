<?php

namespace App\Http\Requests\Addresses;

use App\Enums\AddressType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StoreAddressRequest
 * @package App\Http\Requests\Addresses
 * @OA\Schema(
 * )
 */
class StoreAddressRequest extends FormRequest
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
    public function rules()
    {
        return [
            'address' => 'required|string',
            'number' => 'required|string|max:10',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'nullable|string',
            'country_id' => 'required|integer|exists:countries,id',
            'is_business_user' => 'boolean|nullable',
            'company_name' => 'required_if:is_business_user,true|nullable|string',
            'phone_number' => 'required_if:is_business_user,true|nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:13',
            'tax_nr' => 'required_if:is_business_user,true|nullable|string',
            'full_name' => 'required|string',
            'default' => 'boolean',
            'team_address' => 'required|boolean',
            'team_id' => 'required|integer',
            'team_name' => 'required|string',
            'type' => [
                'nullable',
                'string',
                new Enum(AddressType::class)
            ]
        ];
    }

    protected function prepareForValidation()
    {
        
        $this->merge([
            'team_address' => true,
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
        ]);
    }
}
