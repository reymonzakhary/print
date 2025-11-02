'use strict'

const Discount = use('App/Models/SupplierDiscount')

class DiscountController {

    /**
     * create Discounts
     *
     * @param {Request} ctx.request
     * @param {Response} ctx.response
     */
    async store({ request, response }) {
        try {
            /**
             * collect data from request
             */
            const discountData = request.only([ 'supplier_id', 'reseller_id', 'discount' ])

            /**
             * create Discounts
             */
            const discountSave = await Discount
            .updateOrCreate(
                {"reseller_id": discountData.reseller_id,"supplier_id": discountData.supplier_id},
                {"discount": discountData.discount}
            )

            /**
             * return success
             */
            return response.status(200).json({
                status: 'success',
                data: discountSave
            })
        } catch (e) {
            /**
             * return error
             */
            return response.status(404).json({
                status: 'error',
                message: 'Page not found'
            })
        }
    }

    /**
     * Show Discount - Whole Object
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async show({response, params})
    {
        try {
            /**
             * define Discounts array
             */
            const discount = await Discount.where('supplier_id',params.supplier_id)
                                                .where('reseller_id',params.reseller_id)
                                                    .fetch()
            /**
             * return success
             */
            return response.status(200).json({
                status: 'success',
                data: discount
            })
        } catch (e) {
            /**
             * return error
             */
            return response.status(404).json({
                status: 'error',
                message: 'Page not found'
            })
        }
    }

    /**
     * update discount
     * @param {object} ctx.params
     * @param {Request} ctx.request
     * @param {Response} ctx.response
     */
    async update({ params, request, response }) {
        try {
            /**
             * collect data from request
             */
            const discountData = request.only(['discount'])

            /**
             * update Discounts
             */
            const discount = await Discount
                .query()
                .where('_id', params.id)
                .update({ $set: discountData})

            /**
             * return success
             */
            return response.status(200).json({
                status: 'success'
            })
        } catch (e) {
            /**
             * return error
             */
            return response.status(404).json({
                status: 'error',
                message: 'Page not found'
            })
        }
    }

    /**
     * Show Discount - General Only
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async showGeneral({response, params})
    {
        try {
            /**
             * define Discounts array
             */
            const discount = await Discount.where('supplier_id',params.supplier_id)
                                                .where('reseller_id',params.reseller_id)
                                                    .first()
            var data = discount.discount.general
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
            return response.status(404).json({
                status: 'error',
                message: 'Page not found'
            })
        }
    }

    /**
     * Show Discount - Category Only
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async showCategory({response, params})
    {
      // try {
        /**
         * define Margin array
        */
        const discount = await Discount.where('tenant_id', params.tenant_id)
                                .where('supplier_id', params.supplier_id)
                                .aggregate([
                                              {
                                                "$addFields":{
                                                  "categories":{
                                                    "$objectToArray":"$discount.categories"
                                                  }
                                                }
                                              },
                                              {
                                                "$match":{
                                                  "categories":{
                                                    "$elemMatch": {
                                                      "k": params.category_slug
                                                    }
                                                  }
                                                }
                                              }
                                            ])

        var data = {}

        if(discount.length) {
            data = discount[0].discount.categories[params.category_slug]
        }
        /**
         * return success
         */
        return response.status(200).json(data)
      // } catch (e) {
      //   /**
      //    * return error
      //    */
      //   return response.status(404).json({
      //       status: 'error',
      //       message: 'Page not found'
      //   })
      // }
  }
}

module.exports = DiscountController
