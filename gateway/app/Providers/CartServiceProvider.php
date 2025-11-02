<?php

namespace App\Providers;

use App\Blueprint\BlueprintFactory;
use App\Blueprint\Contract\BlueprintFactoryInterface;
use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Cart\Cart;
use App\Cart\Checkout;
use App\Cart\Contracts\CartContractInterface;
use App\Cart\Contracts\CheckoutContractInterface;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(BlueprintFactoryInterface::class, function () {
            return new BlueprintFactory();
        });

        $this->app->singleton(CartContractInterface::class, function () {
            return new Cart(session(), request());
        });

        $this->app->singleton(CheckoutContractInterface::class, function () {
            return new Checkout(
                session(),
                request(),
                app(CartContractInterface::class),
                app(BlueprintContactInterface::class)
            );
        });


    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
