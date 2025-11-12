<?php

namespace App\Providers;

use App\Actions\Migration\TenancyMigrationAction;
use App\Facades\Settings;
use App\Foundation\FileManager\Contracts\FileManagerInterface;
use App\Foundation\FileManager\FileManagerFactory;
use App\Models\Tenants\Blueprint as ModelBlueprint;
use App\Models\Tenants\Box;
use App\Models\Tenants\Brand;
use App\Models\Tenants\Category;
use App\Models\Tenants\Option;
use App\Models\Tenants\Order;
use App\Models\Tenants\Passport\AuthCode;
use App\Models\Tenants\Passport\Client;
use App\Models\Tenants\Passport\PersonalAccessClient;
use App\Models\Tenants\Passport\RefreshToken;
use App\Models\Tenants\Passport\Token;
use App\Models\Tenants\Product;
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
//            return new FileManager(app(ConfigRepository::class));
            return new FileManagerFactory();
        });


        App::setLocale(Str::lower(Str::lower(Settings::managerLanguage('en')?->value)));
    }

    /**
     * Bootstrap services.
     *
     * @param TenancyMigrationAction $migration
     * @return void
     */
    public function boot(TenancyMigrationAction $migration)
    {
        $migration->register(__DIR__ . '/../Database/Migrations');

        // Load tenant-specific OAuth clients dynamically
        try {
            $passwordClient = Client::where('password_client', true)->first();

            if ($passwordClient) {
                Config::set('services.passport.password_client_id', $passwordClient->id);
                Config::set('services.passport.password_client_secret', $passwordClient->secret);
            } else {
                // Log warning if no password client exists for this tenant
                if (config('app.debug')) {
                    \Log::warning('No password grant client found for tenant', [
                        'tenant_id' => tenant()?->id,
                        'hint' => 'Run: php artisan tenants:passport-install'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if we can't load clients (might be during migration)
            if (config('app.debug')) {
                \Log::debug('Could not load tenant OAuth clients: ' . $e->getMessage());
            }
        }

        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::useRefreshTokenModel(RefreshToken::class);

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
