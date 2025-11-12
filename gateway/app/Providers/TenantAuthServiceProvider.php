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
use App\Models\Tenants\Product;
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

