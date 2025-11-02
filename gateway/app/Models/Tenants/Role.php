<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    use UsesTenantConnection;

    public $guarded = [];
}
