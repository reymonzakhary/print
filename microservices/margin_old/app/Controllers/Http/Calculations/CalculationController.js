'use strict'
const axios = use('axios')
const Env = use('Env')
const Margin = use('App/Models/Margin')

class CalculationController {

  /**
   *  Set price service URL
   *
   *  @param {string PRICE_URL} PRICE_URL
   */
  constructor() {
    this.PRICE_URL = Env.get('PRICE_URL');
  }


  /**
   * get precalculated price
   *
   * @param {object} ctx.params
   * @param {Response} ctx.response
   */
  async index({response, params}) {
    try {

      /* Call service via axios and returns collection */
      let slug = params.collection + '/' + params.category_id + '/' + params.supplier_id

      const call = await axios.get(this.PRICE_URL + slug)

      var priceCollection = call.data

      var prices = priceCollection.data[0].tables

      var category_id = params.category_id

      var fixed, percentage

      /* Check margin existence in DB */
      var margins = await Margin.where('reseller_id', params.reseller_id).first()

      if (margins) {
        /* Check if margin is upon Category or General */
        if (margins.margin.general) /* General margin case */
        {
          if (margins.margin.general.fixed != null) {
            fixed = margins.margin.general.fixed.value
          }

          if (margins.margin.general.percentage != null) {
            fixed = null

            percentage = margins.margin.general.percentage
          }
        }

        if (margins.margin.categories) /*  category margin case (if set will override general) */
        {
          if (margins.margin.categories.hasOwnProperty(category_id)) {
            /* reset margin to override */
            fixed = null, percentage = null

            if (margins.margin.categories[category_id].fixed != null) {
              fixed = margins.margin.categories[category_id].fixed.value
            }

            if (margins.margin.categories[category_id].percentage != null) {
              fixed = null

              percentage = margins.margin.categories[category_id].percentage
            }
          }
        }

        let marginOnCase = {fixed, percentage}

        /* Check type of margin (fixed, or percentage) */
        if (marginOnCase.fixed) {  /* fixed case */
          for (let key in prices) {

            var price = prices[key]

            for (let key1 in price) {
              var inner = price[key1]

              /* add fixed margin */
              inner.normal = parseFloat(inner.normal) + parseFloat(marginOnCase.fixed)
              inner.express = parseFloat(inner.express) + parseFloat(marginOnCase.fixed)
              inner.tomorrow = parseFloat(inner.tomorrow) + parseFloat(marginOnCase.fixed)
            }

          }

          /* override price tables by precalculated prices */
          priceCollection.data[0].tables = prices
        }

        if (marginOnCase.percentage) { /* percentage case */
          for (let key in prices) {

            var price = prices[key]

            for (let key1 in price) {
              var inner = price[key1]

              /* calculate price after percentage addition */
              inner.normal = this.getPriceForPercentage(parseFloat(inner.normal), parseFloat(marginOnCase.percentage))
              inner.express = this.getPriceForPercentage(parseFloat(inner.express), parseFloat(marginOnCase.percentage))
              inner.tomorrow = this.getPriceForPercentage(parseFloat(inner.tomorrow), parseFloat(marginOnCase.percentage))
            }

          }

          /* override price tables by precalculated prices */
          priceCollection.data[0].tables = prices
        }
      }

      /* return price collection after calculation */
      return response.status(200).json({
        status: 'success',
        data: priceCollection
      })
    } catch (e) {
      /* return error */
      return response.status(404).json({
        status: 'error',
        message: 'Page not found'
      })
    }
  }

  /**
   * Calculates price after adding percentage
   *
   * @param {original price} original
   * @param {margin percentage} percentage
   * @param {discount finalPrice} finalPrice
   */
  getPriceForPercentage(original, percentage) {
    let margin = (percentage / 100) * original

    var finalPrice = original + margin

    return finalPrice
  }

}

module.exports = CalculationController
