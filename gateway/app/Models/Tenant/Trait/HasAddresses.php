<?php

declare(strict_types=1);

namespace App\Models\Tenant\Trait;

use App\Models\Country;
use App\Models\Tenant\Address;
use App\Models\Tenant\Addressable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddresses
{
    /**
     * @return MorphToMany
     */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(
            Address::class,
            'addressable',
            'addressable',
            'identifier',
            'address_id',
            'identifier'
        )
            ->using(Addressable::class)
            ->withPivot([
                'type',
                'company_name',
                'full_name',
                'tax_nr',
                'phone_number',
                'team_id',
                'team_name',
                'team_address'
            ]);
    }

    /**
     * @return MorphToMany
     */
    public function delivery_address(): MorphToMany
    {
        return $this->addresses()->wherePivot('type', '=', 'delivery');
    }

    /**
     * @return MorphToMany
     */
    public function invoice_address(): MorphToMany
    {
        return $this->addresses()->wherePivot('type', '=', 'invoice');
    }

    /**
     * @return MorphToMany
     *
     * @deprecated You should use `delivery_address` method instead
     */
    public function address(): MorphToMany
    {
        return $this->addresses()->wherePivot('type', '!=', 'invoice');
    }

    public function formatAddressFromRelation(
        string $relation
    ): ?string
    {
        $address = $this->$relation()->first();
        if(!$address){
            return __("No address assigned");
        }

        if(is_null($address->format_address)){

            $country_name = null;
            if($address->country_id) {
                $country_name = Country::where('id',$address->country_id)->get('name');
            }

            $address->format_address = collect([
                $address->number,
                $address->address,
                $address->city,
                $address->region,
                $address->zip_code,
                $country_name,
            ])
            ->filter()
            ->implode(', ');

            $address->save();
        }
        return $address->format_address;
    }


}
