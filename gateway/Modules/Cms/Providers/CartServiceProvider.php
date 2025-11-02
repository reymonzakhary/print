<?php

namespace Modules\Cms\Providers;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Modules\Cms\Foundation\Cart\Contracts\CartContractInterface::class, function () {
            return new \Modules\Cms\Foundation\Cart\Cart(session(), request());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
