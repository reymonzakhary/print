<?php

use App\Http\Controllers\System\Mgr\Apps\AppController;
use App\Http\Controllers\System\Mgr\Clients\Media\MediaController;
use App\Http\Controllers\System\Mgr\Companies\CompanyController;
use App\Http\Controllers\System\Mgr\Companies\Contracts\ContractController;
use App\Http\Controllers\System\Mgr\Companies\Quotations\QuotationController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Mgr', 'prefix' => 'mgr'], function () {

    Route::group(['namespace' => 'Auth'], function () {
        Route::post('login', 'AuthenticationController@login');
    });

    Route::group(['middleware' => ['auth:api', 'auth.gate', 'restrictAccess']], function () {

        Route::group([
            'namespace' => 'Account',
            'prefix' => 'account',
            'middleware' => 'grant:account'
        ], function () {
            Route::get('me', 'UserController@me')->name('*');
            Route::post('logout', 'UserController@logout')->name('*');
        });

        Route::get('company', [CompanyController::class, 'company'])
        ->middleware('grant:company')
        ->name('company-read');

        Route::get('apps', AppController::class)
            ->middleware('grant:apps')
            ->name('apps-read');

        Route::group([
            'namespace' => 'Companies',
            'prefix' => 'companies',
            'middleware' => 'grant:companies'
        ], function () {
            Route::get('/', [CompanyController::class, 'index'])
                ->name('companies-read');

            Route::group([
                'middleware' => 'grant:companies,contracts'
            ], function () {
                Route::get('/{company}/contracts', [ContractController::class, 'index'])
                    ->name('companies-contracts-read');
                Route::post('/{company}/contracts', [ContractController::class, 'store'])
                    ->name('companies-contracts-create');;
                Route::put('/{company}/contracts/{contract}', [ContractController::class, 'update'])
                    ->name('companies-contracts-update');
            });


            Route::group([
                'namespace' => 'Quotations',
                'middleware' => 'grant:companies,contracts,quotations'
            ], function () {
                Route::get('{company}/quotations/{quotation}', [QuotationController::class, 'index'])
                    ->name('companies-contracts-quotations-read');
                Route::post('{company}/quotations', [QuotationController::class, 'store'])
                    ->name('companies-contracts-quotations-create');;

                Route::post('{company}/contracts/{contract}/quotations/{quotation}/accept', [QuotationController::class, 'accept'])
                    ->name('companies-contracts-quotations-update');
                Route::post('{company}/contracts/{contract}/quotations/{quotation}/decline', [QuotationController::class, 'decline'])
                    ->name('companies-contracts-quotations-update');
            });
        });


        Route::group([
            'namespace' => 'Clients',
            'middleware' => 'grant:clients'
        ], function () {
            Route::resource('clients', 'ClientController')
                ->names([
                    'index' => 'clients-read',
                    'show' => 'clients-read',
                    'store' => 'clients-create',
                    'update' => 'clients-create',
                    'destroy' => 'clients-delete',
                ])
                ->except(['edit']);
            Route::post('clients/{client}/media', [MediaController::class, 'store'])
            ->name('clients-media-create');
        });

    });


//    Route::group([
//        'namespace' => 'Suppliers',
//        'prefix' => 'suppliers',
//        'middleware' => ['signed', 'web']
//    ], function () {
//        Route::get(
//            'invitation/{hostname:host_id}/{company}',
//            [ContractController::class, 'invitation']
//        )->name('suppliers.invitation');
//    });
});
