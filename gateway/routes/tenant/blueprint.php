<?php


// blueprints crud

// add relation

/** post
 * blueprints/{blueprint}/attach
 * model : product
 * model_id: product id,
 * ns : shop, cart,checkout,
 */


use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:blueprints,automation', 'namespace' => 'Blueprints'], function () {

    Route::get('blueprints/namespaces', 'BlueprintController@namespaces')->name('*');

    Route::post('blueprints/{blueprint}/customs/products/{product}', 'BlueprintController@attachToProduct')->name('*');
    Route::post('blueprints/{blueprint}/customs/products/{product}/deattach', 'BlueprintController@deAttachProduct')->name('*');

    Route::resource('blueprints', 'BlueprintController', [
        'names' => [
            'index' => 'blueprints-automation-list',
            'show' => 'blueprints-automation-read',
            'store' => 'blueprints-automation-create',
            'update' => 'blueprints-automation-update',
            'destroy' => 'blueprints-automation-delete'
        ]
    ])->except(['create', 'edit']);

});
