<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthGates;
use App\Http\Middleware\CartMiddleware;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\CtxAuthMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\FileManagerACL;
use App\Http\Middleware\GrantMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RestrictionMiddleware;
use App\Http\Middleware\SetDynamicUserMiddleware;
use App\Http\Middleware\StartSessionReadonly;
use App\Http\Middleware\SwitchConnectionServiceProvider;
use App\Http\Middleware\SystemAuthGates;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCompanyIAM;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        SwitchConnectionServiceProvider::class,
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        AddQueuedCookiesToResponse::class,
//        \App\Http\Middleware\ProfileJsonResponseMiddleware::class,
        HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'system' => [
            EncryptCookies::class,
            StartSession::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            'throttle:600,1',
            SubstituteBindings::class,
        ],

        'api' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            'throttle:600,1',
            SubstituteBindings::class,
            CartMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'auth.ctx' => CtxAuthMiddleware::class,
        'restrictAccess' => RestrictionMiddleware::class,
        'fm-tenant-acl' => FileManagerACL::class,
        'grant' => GrantMiddleware::class,
        'auth.tenant.gate' => AuthGates::class,
        'auth.gate' => SystemAuthGates::class,
        'inertia' => HandleInertiaRequests::class,
        'dynamic.user' => SetDynamicUserMiddleware::class,
    ];
}
