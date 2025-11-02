<?php

namespace App\Providers;

use App\Foundation\Context\ContextHandler;
use App\Foundation\Crypto\Crypto;
use App\Foundation\DesignProviders\DesignProvider;
use App\Foundation\Settings\Contracts\SettingsContract;
use App\Foundation\Settings\Contracts\SettingsContractInterface;
use App\Foundation\Settings\Setting;
use App\Jobs\Middleware\TenantSwitchMiddleware;
use App\Models\Tenants\Address;
use App\Models\Tenants\Company;
use App\Models\Tenants\Context;
use App\Models\Tenants\Lexicon;
use App\Models\Tenants\Order;
use App\Models\Tenants\Sku;
use App\Plugins\Moneys;
use App\Repositories\AddressRepository;
use App\Repositories\LexiconRepository;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Config;
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
            return new ContextHandler(cache: cache(),session: session(), request: request());
        });

        $this->app->singleton('design-provider', function () {
            return new DesignProvider(
                request(),
                new \App\Repositories\DesignProviderTemplateRepository(
                    new \App\Models\Tenants\DesignProviderTemplate
                ),
                new \App\Services\DesignProviders\ConneoPreflightService
            );
        });

        $this->app->singleton('crypto', function () {
            return new Crypto();
        });

//        $this->app->bind(Cms::class, function () {
//            return new Cms(request(), session());
//        });
        // ended

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
        Relation::morphMap([
            'companies' => Company::class,
            'contexts' => Context::class,
            'sku' => Sku::class,
        ]);

        // Use stancl/tenancy helper to check if we're in tenant context
        $tenant = tenant();
        if ($tenant) {
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
}
