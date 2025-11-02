<?php

namespace App\Foundation\ContractManager\Providers;

use App\Foundation\ContractManager\Contracts\ContractServiceInterface;
use App\Foundation\ContractManager\ContractService;
use Illuminate\Support\ServiceProvider;

class ContractServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ContractServiceInterface::class, ContractService::class);

        $this->app->singleton('contract-manager', function ($app) {
            return $app->make(ContractServiceInterface::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
