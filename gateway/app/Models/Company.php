<?php

namespace App\Models;

use App\Models\Traits\GenerateIdentifier;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Company extends Model
{
    use GenerateIdentifier;

    protected $fillable = [
        'name', 'description', 'coc', 'tax_nr', 'email', 'url', 'authorization'
    ];

    protected $casts = [
        'authorization' => AsArrayObject::class
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function suppliers()
    {
        return $this->belongsToMany(Domain::class, 'contracts', 'requester_id', 'receiver_id')->withPivot([
            'st',
            'activated_at',
            'active',
            'url',
            'callback',
            'webhook'
        ]);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'requester_id');
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
