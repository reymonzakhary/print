<?php

namespace App\Models\Tenants\Passport;

use Laravel\Passport\Token as BaseToken;

class Token extends BaseToken
{


    protected $table = 'oauth_access_tokens';
}
