<?php

Route::group(['middleware' => 'grant:print-assortments,printing-methods', 'prefix' => 'printing-methods', 'namespace' => 'PrintingMethods'], function () {
    Route::get('/', 'PrintingMethodController@index')->name('print-assortments-printing-methods-list');
    Route::get('/{printing_method}', 'PrintingMethodController@show')->name('print-assortments-printing-methods-read');
    Route::post('/', 'PrintingMethodController@store')->name('print-assortments-printing-methods-create');
    Route::put('/{printing_method}', 'PrintingMethodController@update')->name('print-assortments-printing-methods-update');
    Route::delete('{printing_method}', 'PrintingMethodController@delete')->name('print-assortments-printing-methods-delete');
});
/**
 * own categories
 */
Route::group(['middleware' => 'grant:print-assortments,categories', 'namespace' => 'Categories'], function () {
    Route::get('/linked/{linked}/categories', 'CategoryController@getByLinked')->name('*');
    Route::get('/categories/{linked}/manifest', 'CategoryManifestController@__invoke')->name('*');
    Route::post('/categories/{slug}/price/buffer', 'PriceGenerateController@generate')->name('*');

    Route::resource('/categories', 'CategoryController', [
        'names' => [
            'index' => 'print-assortments-categories-list',
            'show' => 'print-assortments-categories-read',
            'store' => 'print-assortments-categories-create',
            'update' => 'print-assortments-categories-update',
            'destroy' => 'print-assortments-categories-delete'
        ]
    ])->except([
        'create', 'edit'
    ]);

    Route::post("/categories/{slug}/products/export", "CategoryExportController@__invoke")->name('print-assortments-categories-list');
    Route::post("/categories/{slug}/products/import", "CategoryImportController@import")->name('print-assortments-categories-list');

    /**
     * boops
     */
    Route::group([
        'prefix' => 'categories/{category}/boops',
        'middleware' => 'grant:print-assortments,boops'
    ], function () {
        Route::get('/', 'BoopsController@index')->name('print-assortments-boops-list');
        Route::post('/', 'BoopsController@store')->name('print-assortments-boops-create');
        Route::put('/', 'BoopsController@update')->name('print-assortments-boops-update');
        Route::put('/open-product', 'BoopsController@openProduct')->name('print-assortments-boops-update');
    });

    /**
     * products
     */
    Route::group([
        'prefix' => 'categories/{category}/products',
        'namespace' => 'Products',
        'middleware' => 'grant:print-assortments,products'
    ], function () {

        Route::get('/', 'ProductController@index')
            ->name('print-assortments-products-list');
        Route::get('/{product}', 'ProductController@show')
            ->name('print-assortments-products-read');
        Route::post('filter', 'ProductController@filter')
            ->name('print-assortments-products-list');
//        Route::post('/calculate/prices','CalculateController@index')->name('*');
        Route::post('/calculate/prices', 'PriceController@index')->name('*');

        Route::group([
            'prefix' => 'combinations',
            'middleware' => 'grant:print-assortments,combinations',
        ], function () {
            Route::get('/list', 'ProductCombinationController@index')->name('print-assortments-combinations-list');
            Route::post('/generate', 'ProductCombinationController@generate')->name('print-assortments-combinations-create');
            Route::post('/regenerate', 'ProductCombinationController@regenerate')->name('print-assortments-combinations-create');
            /**
             *  TODO :: Don't have this Methods
             */
//            Route::post('/{combination}','ProductCombinationController@store')->name('print-assortments-combinations-create');
//            Route::delete('/{combination}','ProductCombinationController@destroy')->name('print-assortments-combinations-delete');
//
            Route::post('/{combination}/prices', 'PriceController@store')->name('print-assortments-combinations-create');
        });
    });

    /**
     * own margins
     */
    Route::group(['prefix' => 'categories/{category}/margins',
        'namespace' => 'Margins',
        'middleware' => 'grant:print-assortments,margins'], function () {
        Route::get('/', 'MarginController@show')->name('print-assortments-margins-read');
        Route::put('/', 'MarginController@update')->name('print-assortments-margins-update');
        Route::patch('/', 'MarginController@update')->name('print-assortments-margins-update');
    });

    /**
     * boxes
     */
    Route::group(['namespace' => 'Boxes', 'middleware' => 'grant:print-assortments,boxes'], function () {
        Route::resource('/boxes', 'BoxController', [
            'names' => [
                'index' => 'print-assortments-boxes-list',
                'store' => 'print-assortments-boxes-create',
                'show' => 'print-assortments-boxes-read',
                'update' => 'print-assortments-boxes-update',
                'destroy' => 'print-assortments-boxes-delete'
            ]
        ])->except([
            'create', 'edit'
        ]);


    });



    /**
     * options
     */

    Route::group(['namespace' => 'Options', 'middleware' => 'grant:print-assortments,options'], function () {
        Route::resource('categories.options', 'OptionController', [
            'names' => [
                'show' => 'print-assortments-options-read',
                'update' => 'print-assortments-options-update',
                'destroy' => 'print-assortments-options-delete'
            ]
        ])->only([
            'show', 'update' , 'destroy'
        ]);
    });



    /**
     * variations
     */
    Route::group(['namespace' => 'Variations', 'middleware' => 'grant:print-assortments,variations'], function () {
        Route::resource('/variations', 'VariationController', [
            'names' => [
                'index' => 'print-assortments-variations-list',
                'show' => 'print-assortments-variations-read',
                'store' => 'print-assortments-variations-create',
                'update' => 'print-assortments-variations-update',
                'destroy' => 'print-assortments-variations-delete'
            ]
        ])->except([
            'create', 'edit'
        ]);
    });


});


/**
 * options
 */

Route::group(['middleware' => 'grant:print-assortments,options' , 'namespace' => 'Options'], function () {
    Route::resource('/options', 'OptionController', [
        'names' => [
            'index' => 'print-assortments-options-list',
            'store' => 'print-assortments-options-create',
        ]
    ])->only([
        'store', 'index'
    ]);


});

