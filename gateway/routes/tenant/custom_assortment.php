<?php

use App\Http\Controllers\Tenant\Mgr\Custom\Boxes\BoxTranslationController;
use App\Http\Controllers\Tenant\Mgr\Custom\Brands\BrandTranslationController;
use App\Http\Controllers\Tenant\Mgr\Custom\Categories\CategoryTranslationController;
use App\Http\Controllers\Tenant\Mgr\Custom\Options\OptionTranslationController;
use App\Http\Controllers\Tenant\Mgr\Custom\Products\ProductSelector;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:custom-assortments,brands', 'namespace' => 'Custom\Brands'], function () {
    Route::resource('/brands', 'BrandController', [
        'names' => [
            'index' => 'custom-assortments-brands-list',
            'show' => 'custom-assortments-brands-read',
            'store' => 'custom-assortments-brands-create',
            'update' => 'custom-assortments-brands-update',
            'destroy' => 'custom-assortments-brands-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);
    Route::get('/brands/{brand}/translations', [BrandTranslationController::class, '__invoke'])
        ->name('custom-assortments-brands-list');
});

Route::group(['middleware' => 'grant:custom-assortments,options', 'namespace' => 'Custom\Options'], function () {
    Route::resource('/options', 'OptionController', [
        'names' => [
            'index' => 'custom-assortments-options-list',
            'show' => 'custom-assortments-options-read',
            'store' => 'custom-assortments-options-create',
            'update' => 'custom-assortments-options-update',
            'destroy' => 'custom-assortments-options-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);
    Route::get('/options/{option}/translations', [OptionTranslationController::class, '__invoke'])
        ->name('custom-assortments-options-list');
});


Route::group(['middleware' => 'grant:custom-assortments,boxes', 'namespace' => 'Custom\Boxes'], function () {
    Route::resource('/boxes', 'BoxController', [
        'names' => [
            'index' => 'custom-assortments-boxes-list',
            'show' => 'custom-assortments-boxes-read',
            'store' => 'custom-assortments-boxes-create',
            'update' => 'custom-assortments-boxes-update',
            'destroy' => 'custom-assortments-boxes-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);
    Route::get('/boxes/{box}/translations', [BoxTranslationController::class, '__invoke'])
        ->name('custom-assortments-boxes-list');
});

Route::group(['middleware' => 'grant:custom-assortments,categories', 'namespace' => 'Custom\Categories'], function () {
    Route::resource('/categories', 'CategoryController', [
        'names' => [
            'index' => 'custom-assortments-categories-list',
            'show' => 'custom-assortments-categories-read',
            'store' => 'custom-assortments-categories-create',
            'update' => 'custom-assortments-categories-update',
            'destroy' => 'custom-assortments-categories-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);

    Route::get('/categories/{category}/translations', [CategoryTranslationController::class, '__invoke'])
        ->name('custom-assortments-categories-list');
});


Route::group(['middleware' => 'grant:custom-assortments,products', 'namespace' => 'Custom\Products'], function () {
    Route::post('products/copy', 'ProductController@copy')
        ->name('custom-assortments-products-update');

    Route::get('/products/{product}/package', "ProductsPackageController@index")->name('custom-assortments-products-list');
    Route::post('/products/{product}/package', "ProductsPackageController@update")->name('custom-assortments-products-create');
    Route::delete('/products/{product}/package/{sku}', "ProductsPackageController@destroy")->name('custom-assortments-products-delete');
    Route::get('products/search', 'ProductController@search')->name('custom-assortments-products-list');

    Route::resource('/products', 'ProductController', [
        'names' => [
            'index' => 'custom-assortments-products-list',
            'show' => 'custom-assortments-products-read',
            'store' => 'custom-assortments-products-create',
            'update' => 'custom-assortments-products-update',
            'destroy' => 'custom-assortments-products-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);


    Route::group(["namespace" => 'Stocks'], function () {
        Route::ApiResource('/products/{product}/stocks', 'ProductStocksController', [
            'names' => [
                'index' => 'custom-assortments-products-stocks-list',
                'store' => 'custom-assortments-products-stocks-create',
                'destroy' => 'custom-assortments-products-stocks-delete'
            ]
        ])->except([
            'destroy', 'update'
        ]);
    });

    Route::group(["namespace" => 'Variations'], function () {
        Route::get('/products/{product}/variations', 'ProductVariationController@index')
            ->name('custom-assortments-products-variations-list');
        Route::post('/products/{product}/variations', 'ProductVariationController@store')
            ->name('custom-assortments-products-variations-create');
        Route::put('/products/{product}/variations/{sku}', 'ProductVariationController@update')
            ->name('custom-assortments-products-variations-update');

        Route::group(["namespace" => 'Stocks'], function () {
            Route::get('/products/{product}/variations/{sku}/stocks', 'ProductVariationsStockController@index')
                ->name('custom-assortments-products-variations-stocks-list');
            Route::post('/products/{product}/variations/{sku}/stocks', 'ProductVariationsStockController@store')
                ->name('custom-assortments-products-variations-stocks-create');
        });
    });


    /**
     * product selector
     */

    Route::get('/products/{product}/selector/{option?}', [ProductSelector::class, '__invoke'])->name('custom-assortments-products-list');


    Route::group(['prefix' => 'products', 'namespace' => 'Imports'], function () {
        Route::post('/import', 'ImportController@import')
            ->name('custom-assortments-products-create');
    });
});
