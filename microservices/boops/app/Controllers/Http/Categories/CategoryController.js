'use strict'

const Category = use('App/Models/Category')
const Database = use('Database')
const Helpers = use('Helpers')

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
       * define assortments array
       */
      const categories = await Category.all()

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
   * @param request
   * @returns {Promise<void|*>}
   */
  async match({request, response})
  {
    try {
      let string = request.all()
      let match =  string.name

      const mongoClient = await Database.connect()
      let categories = await mongoClient.collection('categories')
        .find({
            name: eval(`/${match}/i`)
        }).toArray();

        /**
         * return success
         */
        return response.status(200).json({
          status: 'success',
          data: categories
        })
    }
    catch (e) {
      /**
       * return error
       */
      return response.status(400).json({
        status: 'error',
        message: e.message
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
       * collect data from request
       */
      const boopData = request.all()

      /**
       * collect supplier category and boop from request
       */
      // upload image
      const categoryImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(categoryImage){

        await categoryImage.move(Helpers.publicPath('uploads/categories'), {
          name: `${new Date().getTime()}.${categoryImage.subtype}`
        })

        if (!categoryImage.moved()) {
          return categoryImage.error()
        }
      }


      const name = boopData.name
      const image = (categoryImage && categoryImage.moved())?`uploads/categories/${categoryImage.fileName}`:"";

      const category = await Category.findOrCreate({
        "name": name,
        "image": image
      })

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

  async update({params,  request, response })
  {
    try {
      /**
       * collect data from request
       */
      const categoryData = request.all()

      /**
       * collect supplier category from request
       */
      // upload image
      const categoryImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(categoryImage){

        await categoryImage.move(Helpers.publicPath('uploads/categories'), {
          name: `${new Date().getTime()}.${categoryImage.subtype}`
        })

        if (!categoryImage.moved()) {
          return categoryImage.error()
        }
      }


      const name = categoryData.name
      const image = (categoryImage && categoryImage.moved())?`uploads/categories/${categoryImage.fileName}`:"";

      const category = await Category.where({
        "_id":params['id']
      }).first()
      category.name=name;
      if(image)
        category.image=image;
      category.save();
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

}

module.exports = CategoryController
