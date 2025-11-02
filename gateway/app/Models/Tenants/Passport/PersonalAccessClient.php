<?php

namespace App\Models\Tenants\Passport;

use Laravel\Passport\PersonalAccessClient as BaseAccessClient;

class PersonalAccessClient extends BaseAccessClient
{


    protected $table = 'oauth_personal_access_clients';
}
