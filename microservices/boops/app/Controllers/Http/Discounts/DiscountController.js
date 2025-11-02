'use strict'
const Discount = use('App/Models/Discount')
class DiscountsDiscountController {
  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async index({ params, response })
  {
    try {
      let discounts = await Discount.where(
        {'supplier_id': params.supplier},
        {'reseller_id': params.reseller},
      ).fetch()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: discounts
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
  async general({ params, response })
  {
    try {
      let discounts = await Discount.where(
        {'supplier_id': params.supplier},
        {'reseller_id': params.reseller},
      ).fetch()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: discounts.discount.general
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

module.exports = DiscountsDiscountController
