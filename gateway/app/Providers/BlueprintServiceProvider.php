<?php

namespace App\Providers;

use App\Blueprints\Blueprint;
use App\Blueprints\BlueprintFactory;
use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Blueprints\Contracts\BlueprintFactoryInterface;
use Illuminate\Support\ServiceProvider;

class BlueprintServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BlueprintContactInterface::class, function () {
            return new Blueprint(request(), session());
        });

        $this->app->singleton(BlueprintFactoryInterface::class, function () {
            return new BlueprintFactory();
        });

//        $this->app->singleton(PipelineContractInterface::class, function () {
//            return new Pipeline();
//        });
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
