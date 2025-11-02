<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Middleware\CartMiddleware;

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
//Auth::loginUsingId(1);
// Route::group(['middleware' => ['auth.ctx:web'], 'namespace' => "Web\Cart"], function () {
//     Route::resource('cart', CartController::class, [
//         'parameters' => [
//             'cart' => 'product'
//         ]
//     ]);
// });
//Route::any('{uri?}', 'CmsWebController')->where('uri' ,'.*');
Route::any('{uri?}', 'CmsController@index')->where('uri', '^((?!api).)*?')->middleware([ CartMiddleware::class ]);
//Route::post('{uri?}', 'CmsController@store')->where('uri', '^((?!shop|ecommerce).)*?');

//
//Route::get('{uri}{extension?}', function($uri, $extension = null) {
//    return 'File: ' . $uri . '<br>'
//        . 'Extension: ' . $extension;
//})->where([
//    'uri' => '[a-zA-Z0-9-_]+', // the file name (no dots)
//    'extension' => '\..+' // include the dot as the first character of extension
//]);
