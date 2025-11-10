<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
{


    protected $table = 'oauth_auth_codes';
}
