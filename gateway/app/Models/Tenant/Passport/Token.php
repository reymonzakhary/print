<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\Token as BaseToken;

class Token extends BaseToken
{
    protected $table = 'oauth_access_tokens';
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
