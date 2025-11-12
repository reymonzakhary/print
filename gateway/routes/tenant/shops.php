<?php


use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'shops', 'namespace' => 'Shops'], function () {
    Route::group(['prefix' => 'categories', 'namespace' => 'Categories'], function () {
        Route::get('/', 'CategoryController@index')->name('shops-categories-list');
        Route::get('/{category}', 'CategoryController@show')->name('shops-categories-read');

        Route::group(['namespace' => 'Products'], function () {
            Route::post('{category}/products', 'ProductController@index')->name('shops-categories-list');
            Route::post('{category}/products/list', 'ProductController@list')->name('shops-categories-list');
        });
    });
    Route::get('products/{sku}/generator', 'GeneratorController@show')
        ->name('shops-categories-list');
    Route::post('products/{sku}/generator', 'GeneratorController@generate')
        ->name('shops-categories-list');
});
