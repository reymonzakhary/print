<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
//        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapSystemRoutes();
        $this->mapTenantsRoutes();
        $this->mapMediaRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\\System')
            ->group(base_path('routes/web.php'));
        Route::middleware('web')
            ->group(base_path('routes/tenant/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "Internal" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapSystemRoutes(): void
    {
        Route::prefix('api/v1/in')
            ->domain('manager.' . env('TENANT_URL_BASE'))
            ->namespace("$this->namespace\\System")
            ->group(base_path('routes/api.php'));

        Route::prefix('api/v2/in')
            ->domain('manager.' . env('TENANT_URL_BASE'))
            ->middleware('system')
            ->namespace("$this->namespace\\System\\V2")
            ->group(base_path('routes/api_v2.php'));
    }

    /**
     * Define the "external" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTenantsRoutes(): void
    {
        Route::prefix('api/v1')
            ->namespace("$this->namespace\\Tenant")
            ->middleware('api')
            ->group(base_path('routes/tenant/api.php'));
    }

    protected function mapMediaRoutes(): void
    {
        Route::namespace("$this->namespace\\Tenant\Mgr")
            ->middleware('api')
            ->group(base_path('routes/tenant/fileManager.php'));
    }
}
