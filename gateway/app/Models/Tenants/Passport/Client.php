<?php

namespace App\Models\Tenants\Passport;

use App\Models\Tenants\User;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    use UsesTenantConnection;

    protected $table = 'oauth_clients';

    protected $connection = 'tenant';

    protected $guarded = ['tenant'];

    /**
     * Get the user that the client belongs to.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the authentication codes for the client.
     *
     * @return HasMany
     */
    public function authCodes()
    {
        return $this->hasMany(AuthCode::class, 'client_id');
    }

    /**
     * Get all of the tokens that belong to the client.
     *
     * @return HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Token::class, 'client_id');
    }

    /**
     * Set the value of the provider attribute.
     *
     * @return void
     */
    public function setProviderAttribute()
    {
        $this->attributes['provider'] = 'tenant';
    }

    /**
     * Set the value of redirect attribute.
     */
    public function setRedirectAttribute()
    {
        $this->attributes['redirect'] = 'http://' . request()->name . '.' . env('TENANT_URL_BASE');
    }
}
