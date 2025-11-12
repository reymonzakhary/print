<?php

namespace App\Providers;

use App\Models\Passport\AuthCode;
use App\Models\Passport\Token;
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

        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);

        // Universal Mode: Use custom models that automatically add tenant_id
        Passport::useTokenModel(Token::class);
        Passport::useAuthCodeModel(AuthCode::class);

        // Token lifetimes
        Passport::tokensExpireIn(now()->addDays(5));
        Passport::refreshTokensExpireIn(now()->addDays(10));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Ignore migrations - we have custom migrations
        Passport::ignoreMigrations();
    }
}

