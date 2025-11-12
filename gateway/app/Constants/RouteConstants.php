<?php

namespace App\Constants;

class RouteConstants
{
    public const string API_PREFIX = 'api';
    public const string DASHBOARD_PREFIX = 'mgr';

    public const array TENANT_DASHBOARD_MIDDLEWARE = [
        'web',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
    ];

    public const array TENANT_API_MIDDLEWARE = [
        'throttle:60,1',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
    ];
}
