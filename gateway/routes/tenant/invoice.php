<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:invoices', 'namespace' => 'Invoices'], function () {
    Route::resource('invoices', 'InvoiceController', [
        'names' => [
            'index' => 'invoices-list',
            'show' => 'invoices-read',
            'store' => 'invoices-create',
            'update' => 'invoices-update',
            'destroy' => 'invoices-delete'
        ]
    ])->except(['create', 'edit']);
//
//    Route::post('orders/{order}/items/produce', 'ProduceOrderController@order')
//        ->name('orders-items-produce-create');
//
//    Route::post('orders/produce', 'ProduceOrderController@orders')
//        ->name('orders-items-produce-create');
//
//    Route::group(['middleware' => 'grant:orders,history'], function () {
//        Route::get('orders/{order}/history', 'OrderController@history')
//            ->name('orders-history-list');
//    });
//
//    Route::group(['middleware' => 'grant:orders,items', 'namespace' => 'Items'], function () {
//        Route::resource('orders/{order}/items', 'ItemController', [
//            'names' => [
//                'index' => 'orders-items-list',
//                'show' => 'orders-items-read',
//                'store' => 'orders-items-create',
//                'update' => 'orders-items-update',
//                'destroy' => 'orders-items-delete'
//            ]
//        ])->except(['create', 'edit']);
//        Route::group(['namespace' => 'Blueprints'], function () {
//            Route::post('orders/{order}/items/{item}/blueprint/rerun', 'BlueprintController')->name('orders-items-blueprints-update');
//        });
//        Route::group(['namespace' => 'Tags'], function () {
//            Route::post('orders/{order}/items/{item}/tags', 'TagController')->name('orders-items-tags-create');
//        });
//
//        /**
//         * Addresses group
//         */
//        Route::group(['namespace' => 'Addresses'], function () {
//            //@todo post request
//            Route::put('orders/{order}/items/{item}/addresses', 'AddressController@update')
//                ->name('orders-items-addresses-update');
//            Route::patch('orders/{order}/items/{item}/addresses', 'AddressController@update')
//                ->name('orders-items-addresses-update');
//        });
//        /**
//         * media group
//         */
//        Route::group(['namespace' => 'Media'], function () {
//
//            Route::get('orders/{order}/items/{item}/media', 'MediaController@index')
//                ->name('orders-items-media-list');
//            Route::post('orders/{order}/items/{item}/media', 'MediaController@store')
//                ->name('orders-items-media-create');
//            Route::delete('orders/{order}/items/{item}/media/{file_manager}', 'MediaController@destroy')
//                ->name('orders-items-media-delete');
//        });
//        /**
//         * Services group
//         */
//        Route::group(['namespace' => 'Services'], function () {
//            Route::group(['middleware' => 'grant:orders,items_services'], function () {
//                Route::resource('orders/{order}/items/{item}/services', 'ServiceController', [
//                    'names' => [
//                        'index' => 'orders-items-services-list',
//                        'show' => 'orders-items-services-read',
//                        'store' => 'orders-items-services-create',
//                        'update' => 'orders-items-services-update',
//                        'destroy' => 'orders-items-services-delete'
//                    ]
//                ])->except(['create', 'edit']);
//
//                /**
//                 * media services
//                 */
//                Route::group(['namespace' => 'Media'], function () {
//                    Route::get('orders/{order}/items/{item}/services/{service}/media', 'MediaController@index')
//                        ->name('orders-items-services-media-list');
//                    Route::post('orders/{order}/items/{item}/services/{service}/media', 'MediaController@store')
//                        ->name('orders-items-services-media-create');
//                    Route::delete('orders/{order}/items/{item}/services/{service}/media/{file_manager}', 'MediaController@destroy')
//                        ->name('orders-items-services-media-delete');
//                });
//            });
//        });
//
//        /**
//         * produce
//         */
//        Route::post('orders/{order}/items/{item}/produce', 'ProduceItemController')
//            ->name('orders-items-produce-create');
//
//
//        /**
//         * item discounts
//         */
//        Route::group(['middleware' => 'grant:orders,items_discount', 'namespace' => 'Discounts'], function () {
//            Route::post('orders/{order}/items/{item}/discounts', 'DiscountController')
//                ->name('orders-items-discount-create');
//        });
//
//    });
//    // @todo to be tested
//    Route::group(['middleware' => 'grant:orders,services', 'namespace' => 'Services'], function () {
//        Route::get('orders/{order}/services', 'ServiceController@index')->name('orders-services-list');
//        Route::get('orders/{order}/services/{service}', 'ServiceController@show')->name('orders-services-read');
//        Route::post('orders/{order}/services', 'ServiceController@store')->name('orders-services-create');
//        Route::put('orders/{order}/services/{service}', 'ServiceController@update')->name('orders-services-update');
//        Route::delete('orders/{order}/services/{service}', 'ServiceController@destroy')->name('orders-services-delete');
//
//        Route::group(['middleware' => 'grant:orders,services_media', 'namespace' => 'Media'], function () {
//            Route::get('orders/services/{service}/media', 'MediaController@index')->name('orders-services-media-list');
//            Route::post('orders/services/{service}/media', 'MediaController@store')->name('orders-services-media-create');
//            Route::delete('orders/services/{service}/media/{media}', 'MediaController@destroy')->name('orders-services-media-delete');
//        });
//    });
//
//    /**
//     * discounts orders
//     */
//    Route::group(['middleware' => 'grant:orders,discount', 'namespace' => 'Discounts'], function () {
//        Route::post('orders/{order}/discounts', 'DiscountController')->name('orders-discount-create');
//    });
//
//
//    Route::group(['middleware' => 'grant:orders,jobtickets', 'namespace' => 'Items\JobTickets'], function () {
//        Route::post('orders/{order}/jobtickets/{item}/{format}', 'JobTicketController@send')->name('orders-jobtickets-list');
//    });
//
//
//    Route::group(['middleware' => 'grant:orders,notifications', 'namespace' => 'Notifications'], function () {
//        Route::get('orders/{order}/notifications/mails/{template}', 'MailController@show')->name('orders-notifications-list');
//        Route::post('orders/{order}/notifications/mails', 'MailController@email')->name('orders-notifications-create');
////                Route::post('orders/{order}/notifications/acceptance', [MailController::class, 'acceptance']);
//    });

});
