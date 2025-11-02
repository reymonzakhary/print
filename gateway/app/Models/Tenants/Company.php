<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Trait\HasAddresses;
use App\Models\Traits\GenerateIdentifier;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Company extends Model
{
    use UsesTenantConnection, GenerateIdentifier, HasAddresses;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'coc', 'tax_nr', 'email', 'url', 'vat_nr', 'vat_id', 'phone','dial_code'
    ];

    protected $casts = [
        'phone' => 'integer',
        'dial_code' => 'integer'
    ];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return MorphToMany
     */
    public function addresses()
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressable',
            'identifier', 'address_id', 'identifier')
            ->using(Addressable::class)
            ->withPivot('type', 'company_name', 'full_name', 'tax_nr', 'phone_number');
    }


    /**
     * @return BelongsToMany
     */
    public function owendby(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'company_id', 'id');
    }

    /**
     * @params Builder
     * @param Builder $builder
     * @return Builder
     */
    public function scopeMain(
        Builder $builder
    ): Builder
    {
        return $builder->where('user_id', 1);
    }


}
