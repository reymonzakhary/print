<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\GenerateIdentifier;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Context extends Model
{
    use GenerateIdentifier, HasAddresses;

    protected $fillable = ['name', 'config'];

    public $timestamps = false;

    protected $casts = [
        'config' => AsArrayObject::class,
    ];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_contexts')
            ->withPivot('member');
    }

    /**
     * @return HasMany
     */
    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
