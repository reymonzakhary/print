<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['namespace' => "Web\Cart"], function () {
    Route::resource('cart', 'CartController', [
        'parameters' => [
            'cart' => 'product'
        ]
    ]);
});

Route::group(['namespace' => "Web\Orders"], function () {
    Route::resource('orders', 'OrderController');
});
