<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\PersonalAccessClient as BaseAccessClient;

class PersonalAccessClient extends BaseAccessClient
{


    protected $table = 'oauth_personal_access_clients';
}
