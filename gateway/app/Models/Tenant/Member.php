<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\GenerateIdentifier;
use App\Notifications\Tenant\Email\Member\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Passport\HasApiTokens;


/**
 * Class User
 * @package App\Models
 */
class Member extends Authenticatable implements MustVerifyEmail, LaratrustUser
{
    use HasRolesAndPermissions,
        SoftDeletes,
        Notifiable,
        GenerateIdentifier,
        HasApiTokens, HasAddresses,
        CanBeScoped;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'type', 'email_verified_at', 'remember_token'
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

    protected $appends = ["user_resources"];

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
                'member_id' => $value
            ],
            [
                'member_id' => 'integer|min:1'
            ]
        )->validate();

        return $this->where('id', $value)->with('contexts')
            ->whereHas('contexts', fn ($q) =>  $q->where([
                ['member', '=', true]
            ]))
            ->first() ?? abort(404, __('Not Found -- There is no member found'));
    }

    /**
     * @return HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
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
        return $this->belongsToMany(Context::class, 'user_contexts', 'user_id')->withPivot('member');
    }

    /**
     * @param $args
     * @return bool
     */
    public function canAccess($args)
    {
        $ctx = explode('|', $args);
        return (bool)collect($this->contexts)->whereIn('name', $ctx)->count();
    }

    /**
     * @return BelongsToMany
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id');
    }

    /**
     * @return mixed|null
     */
    public function company()
    {
        return $this->companies()->first();
    }

    /**
     * @return BelongsToMany
     */
    public function userGroup()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user', 'user_id', 'user_group_id');
    }

    /**
     * @return BelongsToMany
     */
    public function userTeams()
    {
        return $this->belongsToMany(Team::class, 'user_teams', 'user_id', 'team_id')
            ->withPivot('admin', 'authorizer');
    }

    /**
     * @return BelongsToMany
     */
    public function getUserResourcesAttribute()
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
    )
    {
        return $this->userGroup()->whereIn('id', [$group->id])->exists();
    }

    /**
     * @return BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'role_user', 'user_id', 'team_id');
    }

    /**
     * @param $builder
     * @return mixed
     */
    public function scopeOwner(
        $builder
    )
    {
        return $builder->skip(1)->get();
    }

    /**
     * @return HasMany
     */
    public function settings()
    {
        return $this->hasMany(UserSetting::class, 'user_id', 'id');
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
     * @return HasOne
     */
    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id', 'id');
//        return $this->belongsToMany(Product::class, 'carts')
//            ->withPivot('id','quantity', 'variations', 'product_type')
//            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function authoredOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'author_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id')
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

    /**
     * @return HasMany
     */
    public function authoredQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'author_id', 'id');
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
            new VerifyEmailNotification($uuid ?? tenant()->uuid, $generatePassword)
        );
    }
}
