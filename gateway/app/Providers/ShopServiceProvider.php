<?php

namespace App\Providers;

use App\Shop\Contracts\ShopCategoryInterface;
use App\Shop\Contracts\ShopProductInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ShopServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton(ShopProductInterface::class, function () {
            $type = Str::lower(request()->type) === 'print' ? 'Print' : 'Custom';
            if (class_exists('App\Shop\Product\Shop' . $type . 'Product')) {
                return app('App\Shop\Product\Shop' . $type . 'Product');
            }
        });

        app()->singleton(ShopCategoryInterface::class, function () {
            $type = Str::lower(request()->type) === 'print' ? 'Print' : 'Custom';
            if (class_exists('App\Shop\Category\Shop' . $type . 'Category')) {
                return app('App\Shop\Category\Shop' . $type . 'Category');
            }
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
