<?php

use App\Http\Controllers\System\Mgr\Apps\AppController;
use App\Http\Controllers\System\Mgr\Countries\CountryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\Mgr\Clients\Media\MediaController;


Route::group(['namespace' => 'Mgr', 'domain' => 'manager.' . env('TENANT_URL_BASE')], function () {
    Route::group(["middleware" => "inertia"], function () {
        Auth::routes(['register' => false]);
        Route::get('/', 'HomeController@index')->name('dashboard');

        Route::get('/standardization', 'Categories\CategoryController@page')->name('standardisation');
        Route::get('/tenants', 'Clients\ClientController@page')->name('clients');
        Route::get('apps', AppController::class);

        Route::group(['namespace' => 'Clients'], function () {
            Route::resource('clients', 'ClientController')->except(['edit']);
            Route::post('clients/{hostname}/media', [MediaController::class, 'store']);
        });

        Route::group(['namespace' => 'Users', 'prefix' => 'users'], function () {
            // create user and attach company info to him
            Route::resource('/', 'UserController');
        });

        Route::get('/countries', [CountryController::class, '__invoke']);
        /**
         * group collection of system categories
         */
        Route::group(['namespace' => 'Categories'], function () {
            // categories
            Route::resource('/unlinked/categories', 'UnlinkedCategoryController');
            Route::resource('/unmatched/categories', 'UnmatchedCategoryController');
            Route::resource('/merge/categories', 'MergeCategoryController');

            Route::resource('/categories', 'CategoryController');
            Route::post('/categories/{slug}/attach', 'CategoryController@attach');
            Route::post('/categories/{slug}/detach', 'CategoryController@detach');


            Route::group(['namespace' => 'Boxes', 'prefix' => 'categories/{category}'], function () {
                Route::resource('/boxes', 'BoxController');
                Route::post('/boxes/{box}/attach', 'BoxController@attach');

                Route::group(['namespace' => 'Options', 'prefix' => 'boxes/{box}'], function () {
                    Route::resource('/options', 'OptionController');
                    Route::post('/options/{option}/attach', 'OptionController@attach');
                });
            });

            Route::group(['namespace' => 'Products', 'prefix' => 'categories/{category}'], function () {
                Route::resource('/products', 'ProductController');
            });

            Route::group(['namespace' => 'Suppliers', 'prefix' => 'categories/{category}'], function () {
                Route::post('/suppliers/{supplier}/detach', 'SupplierController@detach');
            });

        });

        /**
         * group collection of system boxes
         */
        Route::group(['namespace' => 'Boxes'], function () {
            Route::resource('/unlinked/boxes', 'UnlinkedBoxController');
            Route::resource('unmatched/boxes', 'UnmatchedBoxController');
            Route::resource('/merge/boxes', 'MergeBoxController');
            Route::resource('boxes', 'BoxController');
            Route::post('/boxes/{box}/attach', 'BoxController@attach');
            Route::resource('/boxes/{box}/relations', 'BoxRelationController');

            Route::group(['namespace' => 'Options', 'prefix' => 'boxes/{box}'], function () {

                Route::resource('options', 'OptionController', [
                    'names' => [
                        'index' => 'access-options',
                        'show' => 'read-options'
                    ]
                ])->except([
                    'create', 'edit', 'store'
                ]);
            });
        });

        /**
         * group collection of system options
         */
        Route::group(['namespace' => 'Options'], function () {
            Route::resource('/unlinked/options', 'UnlinkedOptionController');
            Route::resource('/unmatched/options', 'UnmatchedOptionController');
            Route::resource('options', 'OptionController');
            Route::post('/options/{options}/attach', 'OptionController@attach');
            Route::resource('/options/{option}/relations', 'OptionRelationController');
        });

        /**
         *
         */
        Route::group(['namespace' => 'Clients'], function () {
            Route::get('/tenants', 'ClientController@page');
            Route::resource('clients', 'ClientController');
            //        Route::post('/', 'ClientController@store');
        });
    });

//    Route::group(['namespace' => 'Clients'], function(){
//
//        Route::resource('tenants', 'ClientController');
//    });
});
