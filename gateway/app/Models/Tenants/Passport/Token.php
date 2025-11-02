<?php

namespace App\Models\Tenants\Passport;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Laravel\Passport\Token as BaseToken;

class Token extends BaseToken
{
    use UsesTenantConnection;

    protected $table = 'oauth_access_tokens';
}
