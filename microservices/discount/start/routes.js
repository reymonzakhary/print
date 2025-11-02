'use strict'

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Http routes are entry points to your web application. You can create
| routes for different URLs and bind Controller actions to them.
|
| A complete guide on routing is available here.
| http://adonisjs.com/docs/4.1/routing
|
*/

/** @type {typeof import('@adonisjs/framework/src/Route/Manager')} */
const Route = use('Route')

Route
.group(() => {

  /**
   * Create discount in db
   * supplier access id
   * select reseller id and add discount to it
  */
   Route.post('/', 'Discounts/DiscountController.store').validator("Discounts/StoreDiscount")

  /**
   * Show discount - Whole Object
  */
   Route.get('suppliers/:supplier_id/tenants/:tenant_id', 'Discounts/DiscountController.show')

  /**
   * Show discount - General Rule Only
  */
   Route.get('suppliers/:supplier_id/tenants/:tenant_id/general', 'Discounts/DiscountController.showGeneral')

    /**
     * Show discount - Category Rule Only
    */
   Route.get('suppliers/:supplier_id/tenants/:tenant_id/categories/:category_slug', 'Discounts/DiscountController.showCategory')

  /**
   * Update discount value only by ID
  */
   Route.patch(':id', 'Discounts/DiscountController.update').validator("Discounts/UpdateDiscount")

  /**
   * Get price after adding margin
   */
    Route.get('prices/:supplier_id/tenants/:tenant_id/:category_id/:collection', 'Calculations/CalculationController.index')
})
.prefix('discounts')
