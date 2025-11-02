<?php

namespace App\Services\System;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;

class MigrateService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }


    final public function obtainMigrate($status)
    {
        return $this->makeRequest('get', '/migrate/' . $status);
    }

}
