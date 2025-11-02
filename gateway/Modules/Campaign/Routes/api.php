<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['auth.ctx:mgr', 'auth:tenant', 'auth.tenant.gate']], function () {

    Route::group(['namespace' => 'Campaigns', 'middleware' => 'grant:campaigns'], function () {

        Route::get('campaigns/{campaign}/templates', 'TemplateController@index')->name('campaigns-list');
        Route::post('campaigns/{campaign}/templates', 'TemplateController@store')->name('campaigns-create');
        Route::put('campaigns/{campaign}/templates/{template}', 'TemplateController@update')->name('campaigns-update');
        Route::delete('campaigns/{campaign}/templates/{template}', 'TemplateController@destroy')->name('campaigns-delete');

        Route::post('campaigns/{campaign}/generate', 'CampaignExportController@store');
        Route::delete('campaigns/{campaign}/exports/{export}', 'CampaignExportController@destroy')->name('exports-delete');

        Route::resource('campaigns', 'CampaignController', [
            'names' => [
                'index' => 'campaigns-list',
                'show' => 'campaigns-read',
                'store' => 'campaigns-create',
                'update' => 'campaigns-update',
                'destroy' => 'campaigns-delete'
            ]
        ])->except(['edit', 'create']);


    });
});

