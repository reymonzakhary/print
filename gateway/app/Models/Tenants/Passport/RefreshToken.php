<?php

namespace App\Models\Tenants\Passport;

use Laravel\Passport\RefreshToken as BaseRefreshToken;

class RefreshToken extends BaseRefreshToken
{


    protected $table = 'oauth_refresh_tokens';
}
