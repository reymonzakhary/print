<?php

namespace App\Providers;

use App\Actions\Migration\TenancyMigrationAction;
use App\Facades\Settings;
use App\Foundation\FileManager\Contracts\FileManagerInterface;
use App\Foundation\FileManager\FileManagerFactory;
use App\Models\Tenant\Blueprint as ModelBlueprint;
use App\Models\Tenant\Box;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Category;
use App\Models\Tenant\Option;
use App\Models\Tenant\Order;
use App\Models\Tenant\Passport\AuthCode;
use App\Models\Tenant\Passport\Client;
use App\Models\Tenant\Passport\PersonalAccessClient;
use App\Models\Tenant\Passport\RefreshToken;
use App\Models\Tenant\Passport\Token;
use App\Models\Tenant\Product;
use App\Observers\Blueprints\BlueprintObserver;
use App\Observers\Custom\Boxes\BoxObserver;
use App\Observers\Custom\Brands\BrandObserver;
use App\Observers\Custom\Categories\CategoryObserver;
use App\Observers\Custom\Options\OptionObserver;
use App\Observers\Custom\Products\ProductObserver;
use App\Observers\Orders\OrderObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;

class TenantAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FileManagerInterface::class, function () {
            return new FileManagerFactory();
        });

        try {
            App::setLocale(Str::lower(Settings::managerLanguage('en')?->value ?? 'en'));
        } catch (\Exception $e) {
            App::setLocale('en');
        }
    }

    /**
     * Bootstrap services.
     *
     * @param TenancyMigrationAction $migration
     * @return void
     */
    public function boot(TenancyMigrationAction $migration)
    {
        // Only run if tenant is initialized
        if (!tenancy()->initialized) {
            return;
        }
        $migration->register(__DIR__ . '/../Database/Migrations');


        $tokens = Client::where('password_client', true)->pluck('secret', 'id');
        if ($tokens) {
            collect($tokens)->map(function ($v, $k) {
                Config::set('services.passport.password_client_id', $k);
                Config::set('services.passport.password_client_secret', $v);
            });
        }

//        Passport::useTokenModel(Token::class);
//        Passport::useClientModel(Client::class);
//        Passport::useAuthCodeModel(AuthCode::class);
//        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
//        Passport::useRefreshTokenModel(RefreshToken::class);
        // Enable password grant
        Passport::enablePasswordGrant();
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);
        Passport::tokensExpireIn(now()->addDays(5));
        Passport::refreshTokensExpireIn(now()->addMinutes(10));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        /**
         * observer list
         */
        Order::observe(OrderObserver::class);
        Category::observe(CategoryObserver::class);
        ModelBlueprint::observe(BlueprintObserver::class);
        Brand::observe(BrandObserver::class);
        Box::observe(BoxObserver::class);
        Option::observe(OptionObserver::class);
        Product::observe(ProductObserver::class);


        /**
         * CMS
         */


    }
}
