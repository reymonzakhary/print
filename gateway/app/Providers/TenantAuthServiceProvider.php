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
use App\Models\Tenant\Product;
use App\Observers\Blueprints\BlueprintObserver;
use App\Observers\Custom\Boxes\BoxObserver;
use App\Observers\Custom\Brands\BrandObserver;
use App\Observers\Custom\Categories\CategoryObserver;
use App\Observers\Custom\Options\OptionObserver;
use App\Observers\Custom\Products\ProductObserver;
use App\Observers\Orders\OrderObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        /**
         * Universal Mode for Passport:
         *
         * All Passport data (clients, tokens, etc.) is stored in the central database.
         * Tokens automatically get a tenant_id when created in a tenant context.
         * The central OAuth clients are shared by all tenants.
         *
         * No need to load tenant-specific clients - they're in the central DB!
         */

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
    }
}
