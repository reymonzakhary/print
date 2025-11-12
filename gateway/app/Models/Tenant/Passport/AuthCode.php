<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
{
    protected $table = 'oauth_auth_codes';
//    protected $connection = 'tenant';
    /**
     * Get the current connection name for the model.
     */
//    public function getConnectionName(): \UnitEnum|string|null
//    {
//        if (tenancy()->initialized) {
//            return 'tenant';
//        }
//
//        return $this->connection;
//    }
}
