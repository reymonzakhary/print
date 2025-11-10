<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address', 'number', 'city', 'region',
        'zip_code', 'lat', 'lng', 'country_id',
        'format_address'
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
    public function companies(): MorphToMany
    {
        return $this->morphedByMany(Company::class,
            'addressable', 'addressable', 'identifier', 'address_id', 'id', 'id'
        );
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

}
