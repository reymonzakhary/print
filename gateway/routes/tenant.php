<?php

declare(strict_types=1);

use App\Constants\RouteConstants;
use App\Http\Controllers\Tenant\Mgr\Auth\AuthenticationController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api:tenant',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::post('login', [AuthenticationController::class, 'login'])
        ->name('login.access');
});

//Route::prefix(RouteConstants::API_PREFIX)->group(function () {
//
//    Route::middleware(RouteConstants::TENANT_API_MIDDLEWARE)
////        ->prefix(RouteConstants::DASHBOARD_PREFIX)
////        ->as(RouteConstants::DASHBOARD_PREFIX . '.')
//        ->group(function () {
//            Route::post('login', [AuthenticationController::class, 'login'])
//                ->name('login.access');
//        });
//
////    Route::middleware(RouteConstants::TENANT_MOBILE_MIDDLEWARE)
////        ->prefix(RouteConstants::MOBILE_PREFIX)
////        ->as(RouteConstants::MOBILE_PREFIX . '.')
////        ->group(function () {
////            require __DIR__ . '/Tenant/mobile.php';
////        });
//
//});
