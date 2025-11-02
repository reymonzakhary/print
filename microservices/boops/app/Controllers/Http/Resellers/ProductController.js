'use strict'
const ResellerCategory = use('App/Models/ResellerCategory')
const Product = use('App/Models/Product')
class ProductController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async index({ params, response })
  {
    try {

      /**
       * get assortments ids for supplier
       */
      const sku = await ResellerCategory
        .where({
          'reseller_id': params.reseller,
          'sku': params.sku
        })
        .first()

      /**
       * get assortments-old ids for supplier
       */
      const products = await Product
        .where('category_id', sku.sku)
        .paginate()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: products
      })

    } catch (e) {
      /**
       * return error
       */
      return response.status(400).json({
        status: 400,
        message: e
      })
    }
  }

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async show({ params, response })
  {
    try {

      /**
       * get assortments ids for supplier
       */
      const sku = await ResellerCategory
        .where({
          'reseller_id': params.reseller,
          'sku': params.sku
        })
        .first()

      /**
       * get assortments ids for supplier
       */
      const products = await Product
        .where({'category_id':sku.sku, 'collection' : params.collection})
        .first()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: products
      })

    } catch (e) {
      /**
       * return error
       */
      return response.status(400).json({
        status: 400,
        message: e
      })
    }
  }


}

module.exports = ProductController
