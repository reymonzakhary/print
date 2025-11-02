<?php

use App\Http\Controllers\Tenant\Mgr\Orders\Render\RenderPdfController;
use App\Http\Controllers\Tenant\Mgr\Orders\Transaction\Notification\MailController as TransactionMailController;
use App\Http\Controllers\Tenant\Mgr\Orders\Transaction\Render\RenderController as TransactionRenderController;
use App\Http\Controllers\Tenant\Mgr\Orders\Transaction\TransactionController;
use App\Http\Controllers\Tenant\Mgr\Orders\TrashedOrderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:orders', 'namespace' => 'Orders'], function () {
    Route::resource('orders', 'OrderController', [
        'names' => [
            'index' => 'orders-list',
            'show' => 'orders-read',
            'store' => 'orders-create',
            'update' => 'orders-update',
            'destroy' => 'orders-delete'
        ]
    ])->except(['create', 'edit']);

    Route::group(['namespace' => 'Render'], function () {
        Route::get('orders/{order}/render/pdf', RenderPdfController::class)
            ->name('orders-read');
    });

    Route::group(['middleware' => 'grant:orders,trashed', 'namespace' => 'Trashed'], function () {
        Route::get('orders/trashed', [TrashedOrderController::class, 'index'])->name('orders-trashed-list');
        Route::post('orders/trashed/{order}/restore', [TrashedOrderController::class, 'restore'])->name('orders-trashed-update');
    });

    Route::get('orders/{order}/media', 'Media\MediaController@index')->name('orders-media-list');
    Route::post('orders/{order}/media', 'Media\MediaController@store')->name('orders-media-create');
    Route::delete('orders/{order}/media/{file_manager}', 'Media\MediaController@destroy')->name('orders-media-delete');

    Route::post('orders/{order}/items/produce', 'ProduceOrderController@order')
        ->name('orders-items-produce-create');

    Route::post('orders/produce', 'ProduceOrderController@orders')
        ->name('orders-items-produce-create');

    Route::group(['middleware' => 'grant:orders,history'], function () {
        Route::get('orders/{order}/history', 'OrderController@history')
            ->name('orders-history-list');
    });

    Route::group(['middleware' => 'grant:orders,items', 'namespace' => 'Items'], function () {
        Route::resource('orders/{order}/items', 'ItemController', [
            'names' => [
                'index' => 'orders-items-list',
                'show' => 'orders-items-read',
                'store' => 'orders-items-create',
                'update' => 'orders-items-update',
                'destroy' => 'orders-items-delete'
            ]
        ])->except(['create', 'edit']);
        Route::group(['namespace' => 'Blueprints'], function () {
            Route::post('orders/{order}/items/{item}/blueprint/rerun', 'BlueprintController')->name('orders-items-blueprints-update');
        });
        Route::group(['namespace' => 'Tags'], function () {
            Route::post('orders/{order}/items/{item}/tags', 'TagController')->name('orders-items-tags-create');
        });

        /**
         * Addresses group
         */
        Route::group(['namespace' => 'Addresses'], function () {
            //@todo post request
            Route::put('orders/{order}/items/{item}/addresses', 'AddressController@update')
                ->name('orders-items-addresses-update');
            Route::patch('orders/{order}/items/{item}/addresses', 'AddressController@update')
                ->name('orders-items-addresses-update');
        });
        /**
         * media group
         */
        Route::group(['namespace' => 'Media'], function () {

            Route::get('orders/{order}/items/{item}/media', 'MediaController@index')
                ->name('orders-items-media-list');
            Route::post('orders/{order}/items/{item}/media', 'MediaController@store')
                ->name('orders-items-media-create');
            Route::delete('orders/{order}/items/{item}/media/{file_manager}', 'MediaController@destroy')
                ->name('orders-items-media-delete');
        });
        /**
         * Services group
         */
        Route::group(['namespace' => 'Services'], function () {
            Route::group(['middleware' => 'grant:orders,items_services'], function () {
                Route::resource('orders/{order}/items/{item}/services', 'ServiceController', [
                    'names' => [
                        'index' => 'orders-items-services-list',
                        'show' => 'orders-items-services-read',
                        'store' => 'orders-items-services-create',
                        'update' => 'orders-items-services-update',
                        'destroy' => 'orders-items-services-delete'
                    ]
                ])->except(['create', 'edit']);

                /**
                 * media services
                 */
                Route::group(['namespace' => 'Media'], function () {
                    Route::get('orders/{order}/items/{item}/services/{service}/media', 'MediaController@index')
                        ->name('orders-items-services-media-list');
                    Route::post('orders/{order}/items/{item}/services/{service}/media', 'MediaController@store')
                        ->name('orders-items-services-media-create');
                    Route::delete('orders/{order}/items/{item}/services/{service}/media/{file_manager}', 'MediaController@destroy')
                        ->name('orders-items-services-media-delete');
                });
            });
        });

        /**
         * produce
         */
        // Deprecated: this route is not used anymore
//        Route::post('orders/{order}/items/{item}/produce', 'ProduceItemController')
//            ->name('orders-items-produce-create');


        /**
         * item discounts
         */
        Route::group(['middleware' => 'grant:orders,items_discount', 'namespace' => 'Discounts'], function () {
            Route::post('orders/{order}/items/{item}/discounts', 'DiscountController')
                ->name('orders-items-discount-create');
        });

    });
    // @todo to be tested
    Route::group(['middleware' => 'grant:orders,services', 'namespace' => 'Services'], function () {
        Route::get('orders/{order}/services', 'ServiceController@index')->name('orders-services-list');
        Route::get('orders/{order}/services/{service}', 'ServiceController@show')->name('orders-services-read');
        Route::post('orders/{order}/services', 'ServiceController@store')->name('orders-services-create');
        Route::put('orders/{order}/services/{service}', 'ServiceController@update')->name('orders-services-update');
        Route::delete('orders/{order}/services/{service}', 'ServiceController@destroy')->name('orders-services-delete');

        Route::group(['middleware' => 'grant:orders,services_media', 'namespace' => 'Media'], function () {
            Route::get('orders/services/{service}/media', 'MediaController@index')->name('orders-services-media-list');
            Route::post('orders/services/{service}/media', 'MediaController@store')->name('orders-services-media-create');
            Route::delete('orders/services/{service}/media/{media}', 'MediaController@destroy')->name('orders-services-media-delete');
        });
    });

    /**
     * discounts orders
     */
    Route::group(['middleware' => 'grant:orders,discount', 'namespace' => 'Discounts'], function () {
        Route::post('orders/{order}/discounts', 'DiscountController')->name('orders-discount-create');
    });


    Route::group(['middleware' => 'grant:orders,jobtickets', 'namespace' => 'JobTickets'], function () {
        Route::post('orders/{order}/jobtickets', 'JobTicketController@__invoke')->name('orders-jobtickets-list');
    });

    Route::group(['middleware' => 'grant:orders,notifications', 'namespace' => 'Notifications'], function () {
        Route::get('orders/{order}/notifications/mails/{template}', 'MailController@show')->name('orders-notifications-list');
        Route::post('orders/{order}/notifications/mails', 'MailController@email')->name('orders-notifications-create');
//                Route::post('orders/{order}/notifications/acceptance', [MailController::class, 'acceptance']);
    });

    Route::group([
//        'middleware' => 'grant:orders,transactions',
        'namespace' => 'Transactions'
    ], function () {
        Route::get('orders/{order}/transactions', [TransactionController::class, 'index'])->name('*');
        Route::post('orders/{order}/transactions', [TransactionController::class, 'store'])->name('*');

        Route::get('orders/{order}/transactions/{transaction}', [TransactionController::class, 'show'])
            ->name('*');

        Route::put('orders/{order}/transactions/{transaction}', [TransactionController::class, 'update'])
            ->name('*');

        Route::get('orders/{order}/transactions/{transaction}/render/pdf', [TransactionRenderController::class, 'pdf'])
            ->name('*');

        Route::group([
            'namespace' => 'Notifications'
        ], function () {
            Route::get('orders/{order}/transactions/{transaction}/notifications/template', [TransactionMailController::class, 'show'])
                ->name('*');

            Route::post('orders/{order}/transactions/{transaction}/notifications/mails', [TransactionMailController::class, 'send'])
                ->name('*');
        });
    });
});
