<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\RefreshToken as BaseRefreshToken;

class RefreshToken extends BaseRefreshToken
{
    protected $table = 'oauth_refresh_tokens';
//    protected $connection = 'tenant';
//    public function getConnectionName(): \UnitEnum|string|null
//    {
//        if (tenancy()->initialized) {
//            return 'tenant';
//        }
//
//        return $this->connection;
//    }
}
