'use strict'

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Http routes are entry points to your web application. You can create
| routes for different URL's and bind Controller actions to them.
|
| A complete guide on routing is available here.
| http://adonisjs.com/docs/4.1/routing
|
*/
/** @type {typeof import('@adonisjs/framework/src/Route/Manager')} */

const Route = use('Route')

Route.post('suppliers/:supplier/categories', 'Suppliers/Categories/CategoryController.store')
// categories
Route.resource('categories', 'Categories/CategoryController')
    .validator(new Map([
        [['categories.store'], ['Categories/StoreCategory']],
        [['categories.update'], ['Categories/UpdateCategory']]
    ]))
Route.post('categories/match', 'Categories/CategoryController.match')

// category boxes
Route.resource('categories/:category/boxes', 'Categories/Boxes/BoxController')
    .validator(new Map([
        [['categories.store'], ['Categories/Boxes/StoreCategory']],
        [['categories.update'], ['Categories/Boxes/UpdateCategory']]
    ]))
Route.post('categories/:category/boxes/match', 'Categories/Boxes/BoxController.match')

// boxes options
Route.resource('categories/boxes/:box/options', 'Categories/Options/OptionController')
    .validator(new Map([
        [['categories.store'], ['Categories/Options/StoreOption']],
        [['categories.update'], ['Categories/Options/UpdateOption']]
    ]))
Route.post('categories/boxes/:box/options/match', 'Categories/Options/OptionController.match')

Route.get('categories/:category/boxes', 'Categories/Boxes/BoxController.index')

Route.get('categories/:category/boxes/:box/options', 'Categories/Options/OptionController.index')


Route.post('categories', 'Categories/CategoryController.match')

Route.get('boxes', 'Boxes/BoxController.index')
Route.post('boxes', 'Boxes/BoxController.match')

Route.get('suppliers/:supplier/categories', 'Suppliers/CategoryController.index')
Route.get('suppliers/:supplier/categories/:category', 'Suppliers/CategoryController.show')
Route.post('suppliers/link', 'Resellers/ResellerController.store')

Route.get('resellers/:reseller/categories', 'Resellers/ResellerController.index')
Route.get('resellers/:reseller/categories/:sku', 'Resellers/ResellerController.show')


Route.get('resellers/:reseller/categories/:sku/products', 'Resellers/ProductController.index')
Route.get('resellers/:reseller/categories/:sku/products/:collection', 'Resellers/ProductController.show')
Route.get('resellers/:reseller/categories/:sku/products/:collection/calculate', 'Calculation/CalculateController.index')

Route.get('resellers/:reseller/categories/:sku/margins', 'Margins/MarginController.show')
Route.get('resellers/:reseller/margins', 'Margins/MarginController.index')


Route.get('resellers/:reseller/suppliers/:supplier', 'Discounts/DiscountController.index')
Route.get('resellers/:reseller/suppliers/:supplier/general', 'Discounts/DiscountController.general')
Route.get('resellers/:reseller/suppliers/:supplier/categories', 'Discounts/DiscountController.categories')
Route.get('resellers/:reseller/suppliers/:supplier/categories/:sku', 'Discounts/DiscountController.show')
