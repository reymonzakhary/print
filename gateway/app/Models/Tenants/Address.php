<?php

namespace App\Models\Tenants;

use App\Models\Traits\HasGeometry;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model
{
    use UsesTenantConnection, HasGeometry;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address', 'number', 'city', 'region',
        'zip_code', 'lat', 'lng', 'country_id',
        'format_address', 'floor', 'apartment' , 'neighborhood', 'landmark',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * timestamp set to dates
     * @var array
     */
    protected $dates = [];


    /**
     * Get all the owning addressable models.
     */
    public function users()
    {
        return $this->morphedByMany(User::class,
            'addressable', 'addressable', 'identifier', 'address_id', 'id', 'id'
        );
    }

    /**
     * Get all the owning addressable models.
     */
    public function companies()
    {
        return $this->morphedByMany(Company::class,
            'addressable', 'addressable', 'identifier', 'address_id', 'id', 'id'
        );
    }

    /**
     * @return HasOne
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * @return MorphToMany
     */
    public function contexts()
    {
        return $this->morphedByMany(Context::class,
            'addressable', 'addressable', 'identifier', 'address_id', 'id', 'id'
        );
    }

}
