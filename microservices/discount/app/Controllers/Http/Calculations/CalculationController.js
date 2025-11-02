'use strict'
const axios = use('axios')
const Env = use('Env')
const Discount = use('App/Models/Discount')

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

      /* Check discount existence in DB */
      var discounts = await Discount.where('supplier_id', params.supplier_id)
        .where('reseller_id', params.reseller_id)
        .first()
      if (discounts) {
        /*  Check if discount is upon Category or General */
        if (discounts.discount.general)/* General discount case */
        {
          if (discounts.discount.general.fixed != null) {
            fixed = discounts.discount.general.fixed.value
          }

          if (discounts.discount.general.percentage != null) {
            fixed = null

            percentage = discounts.discount.general.percentage
          }
        }

        if (discounts.discount.categories) /* category discount case (if set will override general) */
        {
          if (discounts.discount.categories.hasOwnProperty(category_id)) {
            /* reset discount to override */
            fixed = null, percentage = null

            if (discounts.discount.categories[category_id].fixed != null) {
              fixed = discounts.discount.categories[category_id].fixed.value
            }

            if (discounts.discount.categories[category_id].percentage != null) {
              fixed = null

              percentage = discounts.discount.categories[category_id].percentage
            }
          }
        }

        let discountOnCase = {fixed, percentage}

        /* Check type of discount (fixed, or percentage) */
        if (discountOnCase.fixed) {  /* fixed case */
          for (let key in prices) {

            var price = prices[key]

            for (let key1 in price) {
              var inner = price[key1]

              /* subtract fixed discount */
              inner.normal = parseFloat(inner.normal) - parseFloat(discountOnCase.fixed)
              inner.express = parseFloat(inner.express) - parseFloat(discountOnCase.fixed)
              inner.tomorrow = parseFloat(inner.tomorrow) - parseFloat(discountOnCase.fixed)
            }

          }

          /* override price tables by precalculated prices */
          priceCollection.data[0].tables = prices
        }

        if (discountOnCase.percentage) { /* percentage case */
          for (let key in prices) {

            var price = prices[key]

            for (let key1 in price) {
              var inner = price[key1]

              /* calculate price after percentage subtraction */
              inner.normal = this.getPriceForPercentage(parseFloat(inner.normal), parseFloat(discountOnCase.percentage))
              inner.express = this.getPriceForPercentage(parseFloat(inner.express), parseFloat(discountOnCase.percentage))
              inner.tomorrow = this.getPriceForPercentage(parseFloat(inner.tomorrow), parseFloat(discountOnCase.percentage))
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
      /* return error  */
      return response.status(404).json({
        status: 'error',
        message: 'Page not found'
      })
    }
  }

  /**
   * Calculates price after subtracting percentage
   *
   * @param {original price} original
   * @param {discount percentage} percentage
   * @param {discount finalPrice} finalPrice
   */
  getPriceForPercentage(original, percentage) {
    let discount = (percentage / 100) * original

    var finalPrice = original - discount

    return finalPrice
  }

}

module.exports = CalculationController
