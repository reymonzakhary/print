<?php

namespace App\Models\Tenants;

use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Warehouse extends Model
{

    use HasFactory, UsesTenantConnection, Slugable, GenerateIdentifier;

    protected $fillable = ['name', 'slug', 'sort', 'description'];

    /**
     * @return MorphToMany
     */
    public function address()
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressable',
            'identifier', 'address_id', 'identifier')
            ->using(Addressable::class)
            ->withPivot('type', 'company_name', 'full_name', 'tax_nr', 'phone_number');
    }

    public function locations(): hasMany
    {
        return $this->hasMany(Location::class);
    }

    public function removeLocationsIfExists()
    {
        return $this->locations()->count() > 0
            ? $this->locations()->delete()
            : true;
    }

    public function removeAddressIfExists()
    {
        return $this->address()->count() > 0
            ? $this->address()->delete()
            : true;
    }
}
