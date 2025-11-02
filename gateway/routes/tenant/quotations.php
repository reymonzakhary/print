<?php

use App\Http\Controllers\Tenant\Mgr\Quotations\Notifications\MailController;
use App\Http\Controllers\Tenant\Mgr\Quotations\QuotationController;
use App\Http\Controllers\Tenant\Mgr\Quotations\Render\RenderPdfController;
use App\Http\Controllers\Tenant\Mgr\Quotations\TrashedQuotationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:quotations', 'namespace' => 'Quotations'], function () {
    Route::group(['middleware' => 'grant:quotations,trashed', 'namespace' => 'Trashed'], function () {
        Route::get('quotations/trashed', [TrashedQuotationController::class, 'index'])->name('quotations-trashed-list');
        Route::get('quotations/trashed/{quotation}', [TrashedQuotationController::class, 'show'])->name('quotations-trashed-read');
        Route::put('quotations/trashed/{quotation}/restore', [TrashedQuotationController::class, 'restore'])->name('quotations-trashed-update');
    });

    Route::group(['middleware' => 'grant:quotations,history'], function () {
        Route::get('quotations/{quotation}/history', 'QuotationController@history')
            ->name('quotations-history-list');
    });

    Route::group(['namespace' => 'Render'], function () {
        Route::get('quotations/{quotation}/render/pdf', RenderPdfController::class)
            ->name('quotations-read');
    });

    Route::resource('quotations', QuotationController::class, [
            'names' => [
                'index' => 'quotations-list',
                'show' => 'quotations-read',
                'store' => 'quotations-create',
                'update' => 'quotations-update',
                'destroy' => 'quotations-delete'
            ]
        ]
    );

    Route::get('quotations/{quotation}/media', 'Media\MediaController@index')->name('quotations-media-list');
    Route::post('quotations/{quotation}/media', 'Media\MediaController@store')->name('quotations-media-create');
    Route::delete('quotations/{quotation}/media/{file_manager}', 'Media\MediaController@destroy')->name('quotations-media-delete');

    Route::post('quotations/{quotation}/decline', [QuotationController::class, 'decline'])->name('*');

    /**
     * discounts orders
     */
    Route::group(['middleware' => 'grant:quotations,discount', 'namespace' => 'Discounts'], function () {
        Route::post('quotations/{quotation}/discounts', 'DiscountController')->name('quotations-discount-create');
    });

    Route::group(['middleware' => 'grant:quotations,services', 'namespace' => 'Services'], function () {
        Route::get('quotations/{quotation}/services', 'ServiceController@index')->name('quotations-services-list');

        Route::get('quotations/{quotation}/services/{service}', 'ServiceController@show')->name('quotations-services-read');
        Route::post('quotations/{quotation}/services', 'ServiceController@store')->name('quotations-services-create');
        Route::put('quotations/{quotation}/services/{service}', 'ServiceController@update')->name('quotations-services-update');
        Route::delete('quotations/{quotation}/services/{service}', 'ServiceController@destroy')->name('quotations-services-delete');

        /**
         * check this one again
         */
        Route::group(['middleware' => 'grant:orders,services_media', 'namespace' => 'Media'], function () {
            Route::get('services/{service}/media', 'MediaController@index')->name('orders-services-media-list');
            Route::post('services/{service}/media', 'MediaController@store')->name('orders-services-media-create');
            Route::delete('services/{service}/media/{media}', 'MediaController@destroy')->name('orders-services-media-delete');
        });

    });

    Route::group(['middleware' => 'grant:quotations,items', 'namespace' => 'Items'], function () {
        Route::get('quotations/{quotation}/items', 'ItemController@index')
            ->name('quotations-items-list');
        Route::post('quotations/{quotation}/items', 'ItemController@store')
            ->name('quotations-items-create');
        Route::put('quotations/{quotation}/items/{item}', 'ItemController@update')
            ->name('quotations-items-update');
        Route::delete('quotations/{quotation}/items/{item}', 'ItemController@destroy')
            ->name('quotations-items-delete');

        /**
         * Addresses group
         */
        Route::group(['namespace' => 'Addresses'], function () {
            //@todo post request
            Route::put('quotations/{quotation}/items/{item}/addresses', 'AddressController@update')
                ->name('quotations-items-addresses-update');
            Route::patch('quotations/{quotation}/items/{item}/addresses', 'AddressController@update')
                ->name('quotations-items-addresses-update');
        });

        /**
         * media group
         */
        Route::group(['namespace' => 'Media'], function () {

            Route::get('quotations/{quotation}/items/{item}/media', 'MediaController@index')
                ->name('quotations-items-media-create');
            Route::post('quotations/{quotation}/items/{item}/media', 'MediaController@store')
                ->name('quotations-items-media-create');
            Route::delete('quotations/{quotation}/items/{item}/media/{media}', 'MediaController@destroy')
                ->name('quotations-items-media-delete');
        });

        /**
         * Services group
         */
        Route::group(['namespace' => 'Services'], function () {
            Route::group(['middleware' => 'grant:quotations,items_services'], function () {
                Route::get('quotations/{quotation}/items/{item}/services', 'ServiceController@index')
                    ->name('quotations-items-services-list');
                Route::get('quotations/{quotation}/items/{item}/services/{service}', 'ServiceController@show')
                    ->name('quotations-items-services-read');
                Route::post('quotations/{quotation}/items/{item}/services', 'ServiceController@store')
                    ->name('quotations-items-services-create');
                Route::put('quotations/{quotation}/items/{item}/services/{service}', 'ServiceController@update')
                    ->name('quotations-items-services-update');
                Route::delete('quotations/{quotation}/items/{item}/services/{service}', 'ServiceController@destroy')
                    ->name('quotations-items-services-delete');

                /**
                 * media services
                 */
                Route::group(['namespace' => 'Media'], function () {
                    Route::get('quotations/{quotation}/items/{item}/services/{service}/media', 'MediaController@index')
                        ->name('quotations-items-services-media-list');
                    Route::post('quotations/{quotation}/items/{item}/services/{service}/media', 'MediaController@store')
                        ->name('quotations-items-services-media-create');
                    Route::delete('quotations/{quotation}/items/{item}/services/{service}/media', 'MediaController@destroy')
                        ->name('quotations-items-services-media-delete');
                });
            });
        });


    });

    Route::group(['middleware' => 'grant:quotations,notifications', 'namespace' => 'Notifications'], function () {
        Route::get('quotations/{quotation}/notifications/template', [MailController::class, 'show'])->name('quotations-notifications-list');
        Route::post('quotations/{quotation}/notifications/mails', [MailController::class, 'send'])->name('quotations-notifications-create');
    });
});
