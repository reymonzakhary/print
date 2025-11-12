<?php


use Illuminate\Support\Facades\Route;

Route::get('system/catalogues/options/{option}', 'Catalogues\SystemCatalogueController@__invoke')->name('print-assortments-system-catalogues-list');
Route::group(['namespace' => 'Catalogues'], function () {
    Route::resource('/catalogues', 'CatalogueController',[
        'names' => [
            'index' => 'print-assortments-catalogues-list',
            'store' => 'print-assortments-catalogues-create',
            'update' => 'print-assortments-catalogues-update',
            'destroy' => 'print-assortments-catalogues-delete'
        ]
    ])->except(['show','create', 'edit']);
});
