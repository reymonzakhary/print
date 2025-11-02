'use strict'
const Category = use('App/Models/Category')
const Database = use('Database')
class CategoryController {

  /**
   *
   * @param request
   * @param params
   * @param response
   * @returns {Promise<void|*>}
   */
  async store({ request, params, response })
  {
    try {
      /**
       * collect data from request
       */
      // const boopData = request.all()

      let string = request.all()
      let match =  string.name.split(' ')
      //
      const mongoClient = await Database.connect()
      let categories = await mongoClient.collection('categories')
        .find({$or:[
            {"name":{"$regex":"letter"}, $options: 'i'},
            {"name":{"$regex": "head"}, $options: 'i'}
          ]}).toArray();
      /**
       * {$or:[
            {"field1":{"$in":["foo","bar"]}},
            {"field2":{"$in":["foo","bar"]}}
          ]}
       */
      // .find({
      //     name: eval(`/${match}$/i`)
      //   }).toArray();

      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: categories
      })


      // /**
      //  * collect supplier category and boop from request
      //  */
      // const supplier_id = boopData.supplier_id
      // const category_id = boopData.sku
      // const reseller_id = boopData.reseller_id
      //
      // /**
      //  * create relation between reseller, supplier, category and boop
      //  */
      // const reseller = await ResellerCategory.findOrCreate({
      //   "sku": category_id,
      //   "supplier_id": supplier_id,
      //   "reseller_id": reseller_id
      // })
      //
      // /**
      //  * return success
      //  */
      // return response.status(200).json({
      //   status: 'success',
      //   data: reseller
      // })
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

module.exports = CategoryController
