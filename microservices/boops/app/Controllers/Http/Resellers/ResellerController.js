'use strict'
const ResellerCategory = use('App/Models/ResellerCategory')
const Boops = use('App/Models/Boop')
const Database = use('Database')
class ResellerController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async index({ params, response })
  {
    try {


      const mongoClient = await Database.connect()
      let categories = await mongoClient.collection('categories')
        .aggregate([
          {
            $lookup:{
              from: "reseller_categories",       // other table name
              let: { sku: "$sku" },
              pipeline: [
                { $match:
                    { $expr:
                        { $and:
                            [
                              { $eq: [ "$sku",  "$$sku" ] },
                              { $eq: [ "$reseller_id", params.reseller ] }
                            ]
                        }
                    }
                },
                { $project: {  reseller_id: 0, sku:0, _id: 0 } }
              ],
              as: "pivot"
            }
          },
          {   $unwind:"$pivot" },
          { $project: {
              _id: 1,
              sku: 1,
              name: 1,
              supplier_id: "$pivot.supplier_id",
            } }
        ]).toArray()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: categories
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
      const category = await Boops
        .where({'category_id': sku.sku})
        .first()

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: category
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
   * @param request
   * @param response
   * @returns {Promise<void|*>}
   */
  async store({ request, response })
  {
    try {

      /**
       * create relation between reseller, supplier, category and boop
       */
      const reseller = await ResellerCategory.findOrCreate(request.all())

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: reseller
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

module.exports = ResellerController
