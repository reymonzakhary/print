<?php

namespace App\Models\Tenants\Passport;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Laravel\Passport\RefreshToken as BaseRefreshToken;

class RefreshToken extends BaseRefreshToken
{
    use UsesTenantConnection;

    protected $table = 'oauth_refresh_tokens';
}
