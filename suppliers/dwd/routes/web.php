<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});



$router->group(['prefix' => 'categories'], function () use ($router) {
    /**
     * Get & Store all assortments from API
     */
    $router->post('/', 'Categories\CategoryController@store');
    /**
     * Get & Store all assortments from API
     */
    $router->get('/', 'Categories\CategoryController@index');
    /**
     * Category ID route group
     */
    $router->group(['prefix' => '/{category_id}'], function () use ($router) {
        /**
         * Get & Store all properties (Boxes) & Options (Options) from API for specific category
         */
        $router->post('/','Boxes\BoxController@store');
        /**
         * Get & Store all properties (Boxes) & Options (Options) from API for specific category
         */
        $router->get('/', 'Boxes\BoxController@index');

        /**
         * collects created products internally and creates BOOPS minifest
         */
        $router->post('/boops','Boops\BoopController@store');

        /**
         * create product, call API for prices
         */
        $router->post('/product-prices', 'ProductPrices\ProductPriceController@store');
        /**
         * create product, call API for prices
         */
        $router->post('/boop-prices', 'BoopPrices\BoopPriceController@store');

        /**
         * find excludes & put it to boops
         */
        $router->get('/excludes', 'ProductPrices\Excludes\ExcludeController@show');
    });

});

$router->group(['prefix' => 'services'], function () use ($router) {
    /**
     * Store assortments to boops service
     */
    $router->post('/assortments','Categories\CategoryController@storeBoopService');
    /**
     *
     */
    $router->get('/assortments/{category_id}/products-prices', 'BoopPrices\BoopPriceController@storePriceService');
    /**
     * Store assortments to boops service
     */
    $router->post('/boxes', 'Boxes\BoxController@storeBoxBoopService');
    /**
     *
     */
    $router->post('/boops', 'Boops\BoopController@storeBoopService');
    /**
     * Store assortments to boops service
     */
    $router->post('/boops/options', 'Boxes\BoxController@storeOptionBoopService');


});





//
//$router->post('products/store', 'Products\ProductController@store');
// /**
//
//$router->group(['prefix' => 'assortments'], function () use ($router) {
//    /**
//     * Store boops to boops service
//     */

//
//    /**
//     * Category ID route group
//     */
//    $router->group(['prefix' => '/{category_id}'], function () use ($router) {
//
//
//
//
//        /**
//         * collects created products internally and creates BOOPS minifest
//         */
//        $router->get('/boops', [
//            'as' => 'boops',
//            'uses' => 'Boops\BoopController@show'
//        ]);
//
