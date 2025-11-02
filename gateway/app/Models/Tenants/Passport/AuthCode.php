<?php

namespace App\Models\Tenants\Passport;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Laravel\Passport\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
{
    use UsesTenantConnection;

    protected $table = 'oauth_auth_codes';
}
