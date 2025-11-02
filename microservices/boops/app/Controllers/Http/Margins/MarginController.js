'use strict'

const Margin = use('App/Models/Margin')

class MarginsMarginController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async index({ params, response })
  {
    try {

      let margins = await Margin.where(
        {'reseller_id': params.reseller}
      ).first()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: margins
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
      let margins = await Margin.where(
        {'reseller_id': params.reseller},
        {$all: {[params.sku]: 'margin.assortments'}}
      ).first()

      let data = []

      if (margins) {
        if(margins.margin.categories !== null)
        {
          if(margins.margin.categories.hasOwnProperty(params.sku)) {
            data = margins.margin.categories[params.sku]
          }
        }
      }

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: data
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

module.exports = MarginsMarginController
