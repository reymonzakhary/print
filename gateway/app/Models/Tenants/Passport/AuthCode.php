<?php

namespace App\Models\Tenants\Passport;

use Laravel\Passport\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
{


    protected $table = 'oauth_auth_codes';
}
