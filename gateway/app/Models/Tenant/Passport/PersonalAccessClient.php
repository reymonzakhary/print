<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\PersonalAccessClient as BaseAccessClient;

class PersonalAccessClient extends BaseAccessClient
{
    protected $table = 'oauth_personal_access_clients';
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
