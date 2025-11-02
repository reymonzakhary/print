<?php

namespace App\Http\Requests\Addresses;

use App\Facades\Context;
use App\Facades\Settings;
use App\Models\Tenants\Address;
use App\Models\Tenants\Item;
use App\Models\Tenants\Member;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\ValidationException;

final class StoreItemAddressRequest extends FormRequest
{
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
     * Retrieves the validation rules for the form.
     *
     * @return array The validation rules.
     */
    public function rules(): array
    {
        $orderOrQuotation = $this->order ?? $this->quotation;

        return [
            'addresses' => [
                'nullable',
                'array',
                new RequiredIf($this->item->delivery_separated)
            ],

            'addresses.*.address' =>
                [
                    'exists:addresses,id',
                    'integer',
                    'required_with_all:addresses',
                    function (string $attribute, int $addressId, Closure $fail) use ($orderOrQuotation): void {
                        if ($user = $orderOrQuotation->orderedBy) {
                            $teamAddress = $user->userTeams->reject(function (Team $team) use ($addressId): bool {
                                return !$team->address()->where('addresses.id', $addressId)->exists();
                            })->map(function (Team $team) use ($addressId): bool {
                                return $team->address()->where('addresses.id', $addressId)->exists();
                            })->first();

                            if (Settings::useTeamAddress()?->value) {
                                if(!$teamAddress) {
                                    throw ValidationException::withMessages([
                                        'address' =>
                                            __(
                                                'The selected Address is not related to the existing team where the user in it.'
                                            )
                                    ]);
                                }
                            } else {
                                if (!$user->addresses()->where('addresses.id', $addressId)->exists() && !$teamAddress) {
                                    throw ValidationException::withMessages([
                                        'address' =>
                                            __('The selected Address is not related to the existing user.')
                                    ]);
                                }
                            }
                        } else {
                            throw ValidationException::withMessages([
                                $attribute =>
                                    __("Please add costumer to the order first.")
                            ]);
                        }
                    }
                ],

            'addresses.*.qty' => 'integer|required_with_all:addresses|min:0',
            'addresses.*.delivery_pickup' => 'boolean|required_with_all:addresses',
            "'addresses.*.address_type" => "nullable|string",
            "'addresses.*.address_company_name" => "nullable|string",
            "'addresses.*.address_full_name" => "nullable|string",
            "'addresses.*.address_tax_nr" => "nullable|string",
            "'addresses.*.address_phone_number" => "nullable|string",
            "'addresses.*.address_team_address" => "nullable|boolean",
            "'addresses.*.address_team_id" => "nullable|integer",
            "'addresses.*.address_team_name" => "nullable|string",

            'address' => 'nullable|integer',

            "address_type" => "nullable|string",
            "address_company_name" => "nullable|string",
            "address_full_name" => "nullable|string",
            "address_tax_nr" => "nullable|string",
            "address_phone_number" => "nullable|string",
            "address_team_address" => "nullable|boolean",
            "address_team_id" => "nullable|integer",
            "address_team_name" => "nullable|string",
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

        // Handle addresses array if present
        if ($this->has('addresses') && is_array($this->addresses)) {
            $processedAddresses = collect($this->addresses)->map(function ($address) {
                if (isset($address['address_team_address'])) {
                    $address['address_team_address'] = filter_var($address['address_team_address'], FILTER_VALIDATE_BOOLEAN);
                }
                return $address;
            })->toArray();

            $this->merge(['addresses' => $processedAddresses]);
        }
    }

    /**
     * Handle the passed validation after a successful validation.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        if(
            $this->quotation->delivery_multiple &&
            $this->quotation->items->where('id', $this->item->id)->first()->pivot->delivery_pickup
        ) {
            if(!$this->address) {
                $this->removeChildrenAddress();
            }else{
                $address = Context::addresses()->firstWhere('id', $this->address);
                if(!$address) {
                    $this->removeChildrenAddress();
                }else{
                    $this->mergeAddressDetails($address, null, null, false);
                }


            }

        } elseif ($user = $this->quotation?->orderedBy) {
            if ($this->address) {
                $this->processAddress($user);
            }

            if ($this->addresses) {
                $this->processAddresses($user);
            }
        }
    }

    /**
     * Process the address details based on the user type and settings.
     *
     * @param User|Member $user The user whose address is being processed.
     * @return void
     */
    private function processAddress(
        User|Member $user
    ): void
    {
        if (Settings::useTeamAddress()?->value) {
            $address = $this->getTeamAddress($user);
            if(!$address) {
                $this->removeChildrenAddress();
            }else{
                $this->mergeAddressDetails(
                    $address,
                    $address->pivot->pivotParent->id,
                    $address->pivot->pivotParent->name,
                    true
                );
            }

        } else {
            $address = $this->getUserAddress($user);
            if($address) {
                $this->mergeAddressDetails($address, null, null, false);
            }else{
                $this->removeChildrenAddress();
            }
        }
    }

    /**
     * Process the addresses for the user.
     *
     * @param User|Member $user The user object.
     * @return void
     */
    private function processAddresses(
        User|Member $user
    ): void
    {
        collect($this->addresses)->map(function ($add) use ($user) {
            if (Settings::useTeamAddress()?->value) {
                $address = $this->getTeamAddress($user, $add['address']);

                $this->mergeAddressDetails(
                    $address,
                    $address->pivot->pivotParent->id,
                    $address->pivot->pivotParent->name,
                    true
                );
            } else {
                $add = is_array($add) ? $add['address'] : $add;
                $address = $this->getUserAddress($user, $add);
                $this->mergeAddressDetails($address, null, null, false);
            }
        });
    }

    /**
     * Retrieves the team address for a user.
     *
     * @param User|Member $user The user for whom to retrieve the team address.
     * @param int|null $addressId The ID of the address to retrieve. If null, the fallback address ID is used.
     * @return Address|null The team address for the user, or null if not found.
     */
    private function getTeamAddress(
        User|Member $user,
        ?int $addressId = null
    ): ?Address
    {
        return $user->userTeams->reject(function ($team) use ($addressId) {
            return !$team->address()->where('addresses.id', $addressId ?? $this->address)->first();
        })->map(function ($team) use ($addressId) {
            return $team->address()->where('addresses.id', $addressId ?? $this->address)->first();
        })->first();
    }

    /**
     * Retrieves the user address.
     *
     * @param User|Member $user The user for whom to retrieve the address.
     * @param int|null $addressId The ID of the address to retrieve. If null, the fallback address ID is used.
     * @return Address|null The user address, or null if not found.
     */
    private function getUserAddress(
        User|Member $user,
        ?int $addressId = null
    ): ?Address
    {
        if ($address = $user->addresses()->where('addresses.id', $addressId ?? $this->address)->first()) {
            return $address;
        }

        return $user->userTeams->reject(function ($team) use ($addressId) {
            return !$team->address()->where('addresses.id', $addressId ?? $this->address)->first();
        })->map(function ($team) use ($addressId) {
            return $team->address()->where('addresses.id', $addressId ?? $this->address)->first();
        })->first();
    }

    /**
     * Merges the details of an address into the current object.
     *
     * @param Address $address The address to merge.
     * @param int|null $teamId The ID of the associated team, if any.
     * @param string|null $teamName The name of the associated team, if any.
     * @param bool $isTeamAddress Indicates if the address is a team address.
     * @return void
     */
    private function mergeAddressDetails(
        Address $address,
        null|int $teamId,
        null|string $teamName,
        bool $isTeamAddress = false
    ): void
    {
        $this->merge([
            "address_type" => $address->pivot->type,
            "address_company_name" => $address->pivot->company_name,
            "address_full_name" => $address->pivot->full_name,
            "address_tax_nr" => $address->pivot->tax_nr,
            "address_phone_number" => $address->pivot->phone_number,
            "address_team_address" => $isTeamAddress,
            "address_team_id" => $teamId,
            "address_team_name" => $teamName,
        ]);
    }

    /**
     * Removes addresses associated with children items.
     */
    private function removeChildrenAddress(): void
    {
        collect($this->item->children()->get())->map(
            function (Item $orderItem): void {
                $orderItem->addresses()->detach();
                $orderItem->delete();
            }
        );
        $this->item->addresses()->detach();
        $this->item->address()->detach();

        $this->merge([
            'address' => null
        ]);
    }
}
