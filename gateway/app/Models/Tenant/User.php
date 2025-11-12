<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\GenerateIdentifier;
use App\Notifications\Tenant\Email\User\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Passport\HasApiTokens;


/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail, LaratrustUser
{
    use HasRolesAndPermissions,
        Notifiable,
        GenerateIdentifier,
        HasApiTokens,
        CanBeScoped,
        HasAddresses,
        SoftDeletes;


    /**
         * The attributes that are mass assignable.
         *
         * @var array
     */
    protected $fillable = [
        'email', 'password', 'type', 'email_verified_at', 'remember_token', 'type'
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
     * @var string[]
     */
    protected $appends = [
        "user_resources",
//        "mgrAccessAsMember"
    ];

    /**
     * @param mixed $value
     * @param null $field
     *
     * @return Model|void|null
     *
     * @throws ValidationException
     */
    public function resolveRouteBinding($value, $field = null)
    {
        Validator::make(
            [
                'user_id' => $value
            ],
            [
                'user_id' => 'integer|min:1'
            ]
        )->validate();

        return $this->where('id', $value)->with('contexts')
            ->whereHas('contexts', fn($q) => $q->where([
                ['member', '=', false]
            ]))
            ->first() ?? abort(404, __('Not Found -- There is no user found'));
    }

    /**
     * Determine if the user is a member.
     *
     * @return bool
     */
    public function mgrAccessAsMember(): bool
    {
        return DB::table('user_contexts')
            ->where('user_id', $this->id)
            ->where('member', true)
            ->where('context_id', 1)
            ->exists();
    }

    /**
     * Get the value of the isMember attribute.
     *
     * @return bool
     */
    public function getMgrAccessAsMemberAttribute(): bool
    {
        return $this->mgrAccessAsMember();
    }

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
     * @param $builder
     */
    public function scopeIsNotOwner(
        $builder
    )
    {
        $builder->where('id', '!=', 1);
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * @return mixed|null
     */
    public function invoiceAddress(): mixed
    {
        return $this->addresses()
            ->wherePivot('type', 'invoice')
            ->orWherePivot('default', true)
            ->first();
    }

    /**
     * @return BelongsToMany
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class, 'user_contexts')
            ->withPivot('member');
    }

    /**
     * @param $args
     * @return bool
     */
    public function canAccess($args): bool
    {
        $ctx = explode('|', $args);
        dd($this->contexts);
        return (bool)collect($this->contexts)->whereIn('name', $ctx)->count();
    }

    /**
     * @return BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * @return BelongsToMany
     */
    public function userGroup(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user');
    }

    /**
     * @return BelongsToMany
     */
    public function userTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'user_teams')
            ->withPivot('admin', 'authorizer');
    }

    /**
     * @return Collection
     */
    public function getUserResourcesAttribute(): Collection
    {
        return $this->userTeams->map(function ($team) {
            return $team->resourceGroups()->with('resources')->get()->map(function ($resourceGroup) {
                return $resourceGroup->resources;
            });
        })->flatten(2);
    }

    /**
     * @param $group
     * @return bool
     */
    public function hasAccess(
        $group
    ): bool
    {
        return $this->userGroup()->whereIn('id', [$group->id])->exists();
    }

    /**
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'role_user');
    }

    /**
     * @param $builder
     * @return mixed
     */
    public function scopeOwner(
        $builder
    ): mixed
    {
        return $builder->where('id',1);
    }

    /**
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * @return HasOne
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
//        return $this->belongsToMany(Product::class, 'carts')
//            ->withPivot('id','quantity', 'variations', 'product_type')
//            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * @return HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class)
            ->where('type', true);
    }

    /**
     * @return HasMany
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'user_id')
            ->where('type', false);
    }

    public function fullname()
    {
        return $this->profile->fullname;
    }

    /**
     * Email verification
     */
    public function sendApiEmailVerificationNotification($uuid = null, $generatePassword = false): void
    {
        $this->notify(
            new VerifyEmailNotification($uuid ?? tenant()->id, $generatePassword)
        );
    }

    /**
     * @return int
     */
    public function orderCount(): int
    {
        $this->load('orders');
        return $this->orders()->count();
    }

    /**
     * @return int
     */
    public function quotationCount(): int
    {
        $this->load('quotations');
        return $this->quotations()->count();
    }
}
