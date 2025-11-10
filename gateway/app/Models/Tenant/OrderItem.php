<?php

namespace App\Models\Tenant;

use App\Models\Traits\GenerateIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class OrderItem extends Model
{
    use GenerateIdentifier;

    protected $fillable = [
        'qty', 'delivery_pickup', 'shipping_cost'
    ];

    /**
     * @return MorphToMany
     */
    public function addresses()
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressable',
            'identifier', 'address_id', 'identifier')
            ->using(Addressable::class)
            ->withPivot('type', 'company_name', 'full_name', 'tax_nr', 'phone_number',
                'phone_number', 'team_id', 'team_name','team_address');
    }

}
