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
     * Create margin in db
     * reseller id
     * margin object
    */
    Route.post('/', 'Margins/MarginController.store').validator("Margins/StoreMargin")

    /**
     * Show margin - Complete Object
    */
    Route.get('suppliers/:supplier_id', 'Margins/MarginController.show')


    /**
     * Show margin - General Rule Only
    */
    Route.get('suppliers/:supplier_id/general', 'Margins/MarginController.showGeneral')

    /**
     * Update margin value only by ID
     */
    Route.patch('suppliers/:supplier_id/general', 'Margins/MarginController.updateGeneral')

    /**
      * Show margin - Category Rule Only
    */
    Route.get('tenants/:tenant_id/categories/:category_slug', 'Margins/MarginController.showCategory')

    /**
     * Update margin value only by ID
    */
    Route.patch('tenants/:tenant_id/categories/:category_slug', 'Margins/MarginController.update').validator("Margins/UpdateMargin")
    Route.put('tenants/:tenant_id/categories/:category_slug', 'Margins/MarginController.update').validator("Margins/UpdateMargin")

    /**
     * Get price after adding margin
     */
    Route.get('prices/:supplier_id/:reseller_id/:category_id/:collection', 'Calculations/CalculationController.index')
  })
  .prefix('margins')


  /**
   * Margin on Users Route Group
   */
  Route
    .group(() => {
      /**
        * Create margin in db
        * reseller id
        * user_id
        * margin object
      */
      Route.post('/', 'Margins/Users/MarginController.store').validator("Margins/Users/StoreMargin")

      /**
       * Show user's margin - Complete Object
      */
      Route.get('/:reseller_id/:user_id', 'Margins/Users/MarginController.show')


      /**
       * Show user's margin - General Rule Only
      */
      Route.get('/:reseller_id/:user_id/general', 'Margins/Users/MarginController.showGeneral')

      /**
        * Show user's margin - Category Rule Only
      */
      Route.get('/:reseller_id/:user_id/categories/:category_id', 'Margins/Users/MarginController.showCategory')

      /**
       * Update user's margin value only by ID
      */
      Route.patch(':id', 'Margins/Users/MarginController.update').validator("Margins/Users/UpdateMargin")
  })
  .prefix('margins/users')


  /**
   * Margin on User Groups Route Group
   */
  Route
    .group(() => {
      /**
        * Create margin in db
        * reseller id
        * user_group_id
        * margin object
      */
      Route.post('/', 'Margins/Users/Groups/MarginController.store').validator("Margins/Users/Groups/StoreMargin")

      /**
       * Show user group's margin - Complete Object
      */
      Route.get('/:reseller_id/:user_group_id', 'Margins/Users/Groups/MarginController.show')


      /**
       * Show user group's margin - General Rule Only
      */
      Route.get('/:reseller_id/:user_group_id/general', 'Margins/Users/Groups/MarginController.showGeneral')

      /**
        * Show user group's margin - Category Rule Only
      */
      Route.get('/:reseller_id/:user_group_id/categories/:category_id', 'Margins/Users/Groups/MarginController.showCategory')

      /**
       * Update user group's margin value only by ID
      */
      Route.patch(':id', 'Margins/Users/Groups/MarginController.update').validator("Margins/Users/Groups/UpdateMargin")
  })
  .prefix('margins/users/groups')
