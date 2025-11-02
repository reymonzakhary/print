'use strict'
const ResellerCategory = use('App/Models/ResellerCategory')
const Discount = use('App/Models/Discount')
const Margin = use('App/Models/Margin')
const Database = use('Database')
class CalculateController {

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
      const supplier = await ResellerCategory
        .where({'reseller_id': params.reseller, 'sku': params.sku})
        .first()

      let margins = await Margin.where(
        {'reseller_id': params.reseller}
      ).first()

      let discounts = await Discount.where({
          'reseller_id': params.reseller,
          'supplier_id' : supplier.supplier_id
        }).first()

      /**
       * get price
       */
      const mongoClient = await Database.connect()
      let prices = await mongoClient.collection('products')
        .aggregate([

          {$match: {"collection": params.collection}},
          {$match: {"category_id": params.sku}},
          {
            "$project": {
              prices: {
                $filter: {
                  "input": "$prices",
                  "as": "price",
                  "cond": {
                    "$and": [
                      {$eq: ["$$price.supplier_id", supplier.supplier_id]},
                    ]
                  }
                }
              }
            }
          },
          {$unwind: '$prices'}
        ]).toArray()


      /**
       * get margins
       * @type {number}
       */
      let activeMargins = 0
      const marginArray =
        margins.margin.categories[params.category_id] || margins.margin.general
        marginArray !== null ?
        activeMargins = marginArray.find(element => element.status === true) :
        activeMargins = 0


      /**
       * get discount
       */
      let activeDiscounts = 0
      const discountArray =
        discounts.discount.categories[params.sku]
        ||
        discounts.discount.general
        discountArray !== null ?
        activeDiscounts = discountArray.find(element => element.status === true) :
        activeDiscounts = 0

      let pricesData = []
      /**
       * loop through priceData
       */
      for (let [PriceTableKey, PriceTableValue] of Object.entries(prices)) {
        let Quantity = PriceTableValue.prices.tables.qty
        let PrintingPrice = PriceTableValue.prices.tables.p
        let pm = PriceTableValue.prices.tables.pm
        let dlv = PriceTableValue.prices.tables.dlv
        let ppp = PriceTableValue.prices.tables.ppp

        /**
         * check discount modes
         */
        let DiscountType = 0
        let DiscountValue = 0
        if (activeDiscounts !== 0) {
          for (let [DiscountKey, DiscountRow] of Object.entries(activeDiscounts.slots)) {
            if (activeDiscounts.mode === 'run') {
              if (Quantity >= DiscountRow.from && Quantity <= DiscountRow.to) {
                DiscountType = DiscountRow.type
                DiscountValue = DiscountRow.value
              }
            } else if (activeDiscounts.mode === 'price') {
              if (PrintingPrice >= DiscountRow.from && PrintingPrice <= DiscountRow.to) {
                DiscountType = DiscountRow.type
                DiscountValue = DiscountRow.value
              }
            }
          }
        }

        /**
         * check margin modes
         */
        let MarginType = 0
        let MarginValue = 0
        if (activeMargins !== 0) {
          for (let [MarginKey, MarginRow] of Object.entries(activeMargins.slots)) {
            if (activeMargins.mode === 'run') {
              if (Quantity > MarginRow.from && Quantity <= MarginRow.to) {
                MarginType = MarginRow.type
                MarginValue = MarginRow.value
              }
            } else if (activeMargins.mode === 'price') {
              if (PrintingPrice > MarginRow.from && PrintingPrice <= MarginRow.to) {
                MarginType = MarginRow.type
                MarginValue = MarginRow.value
              }
            }
          }
        }



        /** check buying price */
        let buying_price = PrintingPrice
        if (
          DiscountType !== 0 &&
          DiscountValue !== 0
        ) {
          buying_price = (DiscountType === 'percentage') ?
            PrintingPrice - (PrintingPrice * DiscountValue) / 100 :
            PrintingPrice - DiscountValue
        }

        /** check selling price */
        let selling_price = PrintingPrice
        if (
          MarginType !== 0 &&
          MarginValue !== 0
        ) {
          selling_price = (MarginType === 'percentage') ?
            PrintingPrice + (PrintingPrice * MarginValue) / 100 :
            PrintingPrice + MarginValue
        }

        /**
         * attach new values to object
         */
        PriceTableValue['prices'] ={
          "pm": pm,
          "qty": Quantity,
          "dlv": dlv,
          "p": PrintingPrice,
          "ppp": ppp,
          "gross_price": PrintingPrice,
          "buying_price": buying_price,
          "selling_price": selling_price,
          "profit": selling_price - buying_price,
          "discount": {
            "type": DiscountType,
            "value": DiscountValue
          },
          "margin": {
            "type": MarginType,
            "value": MarginValue
          }
        }

      }



      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: prices
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

module.exports = CalculateController
