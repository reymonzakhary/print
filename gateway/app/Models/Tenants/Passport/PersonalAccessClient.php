<?php

namespace App\Models\Tenants\Passport;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Laravel\Passport\PersonalAccessClient as BaseAccessClient;

class PersonalAccessClient extends BaseAccessClient
{
    use UsesTenantConnection;

    protected $table = 'oauth_personal_access_clients';
}
