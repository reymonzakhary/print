'use strict'
const Category = use('App/Models/Category')
const SupplierCategory = use('App/Models/SupplierCategory')
const Boops = use('App/Models/Boop')


class CategoryController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void>}
   */
  async index({ params, response })
  {

    try {

      /**
       * get assortments ids for supplier
       */
      const sku = await SupplierCategory
        .where({'tenant_id': params.supplier})
        .pluck('category')

      /**
       * define assortments array
       */
      const categories = await Category.whereIn('_id',sku).fetch()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: categories
      })

    }catch (e) {
      /**
       * return error
       */
      return response.status(404).json({
        status: 'error',
        message: e.message
      })
    }
  }


  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void>}
   */
  async show({ params, response })
  {

    try {

      /**
       * get assortments ids for supplier
       */
      const category = await Boops
        .where({'category_id': params.category})
        .first()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: category
      })

    }catch (e) {
      /**
       * return error
       */
      return response.status(404).json({
        status: 'error',
        message: e.message
      })
    }
  }
}

module.exports = CategoryController
