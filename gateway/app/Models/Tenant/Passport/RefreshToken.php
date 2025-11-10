<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\RefreshToken as BaseRefreshToken;

class RefreshToken extends BaseRefreshToken
{


    protected $table = 'oauth_refresh_tokens';
}
