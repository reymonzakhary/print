<?php

namespace App\Models;

use App\Models\Traits\GenerateIdentifier;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable implements LaratrustUser
{
    use Notifiable,
        GenerateIdentifier,
        HasRolesAndPermissions,
        UsesSystemConnection,
        HasApiTokens ,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];


    /**
     * Is this user the "organization" owner.
     *
     * @return boolean
     */
    public function isOwner(): bool
    {
        // We assume the superadmin is the first user in the DB.CompanyResource.php
        // Feel free to change this logic.
        return $this->getKey() === 1;
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'identifier', 'identifier');
    }

    /**
     * @return BelongsToMany
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user');
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }


    /**
     * @return MorphToMany
     */
    public function addresses()
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressable',
            'identifier', 'address_id', 'identifier')
            ->using(Addressable::class)
            ->withPivot('type', 'company_name', 'full_name', 'tax_nr', 'phone_number', 'default');
    }

}
