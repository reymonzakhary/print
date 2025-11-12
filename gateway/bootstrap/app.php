<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Central/Manager routes (no tenancy)
            Route::middleware('web')
                ->domain('manager.{domain}')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api/v2/in')
                ->domain('manager.' . env('TENANT_URL_BASE'))
                ->group(base_path('routes/api.php'));

            // Tenant WEB routes
            Route::middleware([
                'web',
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
                \App\Http\Middleware\SwitchConnectionServiceProvider::class,
            ])->group(base_path('routes/tenant/web.php'));

            // Tenant API routes
            Route::middleware([
                'api',
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
                \App\Http\Middleware\SwitchConnectionServiceProvider::class,
            ])
                ->prefix('api/v1')
                ->group(base_path('routes/tenant/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware (runs for ALL requests)
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Web middleware group (don't add tenancy here, it's in route files)
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API middleware group (don't add tenancy here, it's in route files)
        $middleware->api(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Route-specific middleware aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognition' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // Your custom middleware
            'auth.ctx' => \App\Http\Middleware\CtxAuthMiddleware::class,
            'restrictAccess' => \App\Http\Middleware\RestrictionMiddleware::class,
            'fm-tenant-acl' => \App\Http\Middleware\FileManagerACL::class,
            'grant' => \App\Http\Middleware\GrantMiddleware::class,
            'auth.tenant.gate' => \App\Http\Middleware\AuthGates::class,
            'auth.gate' => \App\Http\Middleware\SystemAuthGates::class,
            'inertia' => \App\Http\Middleware\HandleInertiaRequests::class,
            'dynamic.user' => \App\Http\Middleware\SetDynamicUserMiddleware::class,
            'cart' => \App\Http\Middleware\CartMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
