<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
//        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // Enable password grant
        if (!tenancy()->initialized) {
            Passport::enablePasswordGrant();

            $this->commands([
                InstallCommand::class,
                ClientCommand::class,
                KeysCommand::class,
            ]);
            Passport::tokensExpireIn(now()->addMinutes(30));
            Passport::refreshTokensExpireIn(now()->addDays(10));
            Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        }

    }
}
