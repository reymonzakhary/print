<?php

namespace App\Providers;

use App\Foundation\Context\ContextHandler;
use App\Foundation\Crypto\Crypto;
use App\Foundation\DesignProviders\DesignProvider;
use App\Foundation\Settings\Contracts\SettingsContract;
use App\Foundation\Settings\Contracts\SettingsContractInterface;
use App\Foundation\Settings\Setting;
use App\Jobs\Middleware\TenantSwitchMiddleware;
use App\Models\Tenant\Address;
use App\Models\Tenant\Company;
use App\Models\Tenant\Context;
use App\Models\Tenant\Lexicon;
use App\Models\Tenant\Order;
use App\Models\Tenant\Sku;
use App\Repositories\AddressRepository;
use App\Repositories\LexiconRepository;
use App\Repositories\OrderRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageManagerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // @todo have to be removed or updated with switch tenant connection

        $referer = request()->headers->get('referer');
        $host = request()->headers->get('host');
        $subdomain = Str::before($host, '.');
        if ($referer !== null && $referer !== $host && $subdomain !== 'manager') {
            $referer = str_replace(["http://", "https://", "ws://", "://", ":3000", ":6001"], "", $referer);
            $referer = explode('/', $referer)[0];
            Config::set('session.connection', 'tenant');
            request()->headers->set('host', $referer);
            request()->headers->set('referer', $referer);
        }

        $this->app->singleton(SettingsContractInterface::class, function () {
            return new SettingsContract();
        });

        $this->app->singleton('settings', function () {
            return new Setting(request(), session(), cache());
        });

        $this->app->singleton('context', function () {
            return new ContextHandler(cache: cache(), session: session(), request: request());
        });

        $this->app->singleton('design-provider', function () {
            return new DesignProvider(
                request(),
                new \App\Repositories\DesignProviderTemplateRepository(
                    new \App\Models\Tenant\DesignProviderTemplate
                ),
                new \App\Services\DesignProviders\ConneoPreflightService
            );
        });

        $this->app->singleton('crypto', function () {
            return new Crypto();
        });

        if ($this->app->environment('local')) {
//            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
//            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(ImageManagerInterface::class, function () {
            return new ImageManager(new GdDriver());
        });

        $this->app->singleton(AddressRepository::class, function () {
            return new AddressRepository(new Address());
        });

        $this->app->singleton(OrderRepository::class, function () {
            return new OrderRepository(new Order());
        });

        $this->app->singleton(LexiconRepository::class, function () {
            return new LexiconRepository(new Lexicon());
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot()
    {
        // Configure rate limiting
        $this->configureRateLimiting();

        Relation::morphMap([
            'companies' => Company::class,
            'contexts' => Context::class,
            'sku' => Sku::class,
        ]);

        // Use domain-based resolution for multi-tenancy
        // This ensures proper tenant isolation and Reverb compatibility
        $domain = domain();

        if ($domain && $domain->domain) {
            config(['database.default' => 'tenant']);
            if (config('app.env') === 'production') {
                $this->app['request']->server->set('HTTPS', 'on');
            }
        }

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        app()->make(Dispatcher::class)->pipeThrough([TenantSwitchMiddleware::class]);
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiter - 60 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Web rate limiter - 100 requests per minute per IP
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // Global rate limiter - 600 requests per minute (as you had in Kernel)
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(600)->by($request->ip());
        });

        // Login rate limiter - prevent brute force attacks
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . '|' . $request->input('email'));
        });

        // Higher limit for authenticated API users
        RateLimiter::for('api-authenticated', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(200)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Strict rate limiter for sensitive operations
        RateLimiter::for('strict', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
