'use strict'

const Box = use('App/Models/Box')
const Category = use('App/Models/Category')
const CategoryBoxes = use('App/Models/CategoryBoxes')
const Helpers = use('Helpers')

const Database = use('Database')
class BoxController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void>}
   */
  async index({ params, response })
  {

    try {
      const cateBoxes = await CategoryBoxes.where({
        category_id: params.category
      }).first();
      let boxes = [];
      if(cateBoxes) {
        // return response.send(cateBoxes.boxes)
        const mongoClient = await Database.connect()
        boxes = await mongoClient.collection('boxes')
        .find({
            '_id': {$in:  cateBoxes.boxes}
        }).toArray();
      }
      
      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: boxes
      });
      

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
      let boxes = await mongoClient.collection('boxes')
        .find({
            name: eval(`/${match}/i`)
        }).toArray();

        /**
         * return success
         */
        return response.status(200).json({
          status: 'success',
          data: boxes
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
  async store({ params, request, response })
  { 
    try {
      /**
       * collect data from request
       */
      const mongoClient = await Database.connect()
      
      // check category before store box and categoryboxes document
      let category = await mongoClient.collection('categories')
                      .find({
                          _id: params.category
                      }).toArray();
      if(!category)
        return response.status(404).json({
          status: 'success',
          message: 'category not found'
        })

      const boxData = request.all()
      /**
       * collect category boxes from request
       */
      // upload image
      const boxImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(boxImage){

        await boxImage.move(Helpers.publicPath('uploads/boxes'), {
          name: `${new Date().getTime()}.${boxImage.subtype}`
        })          
        
        if (!boxImage.moved()) {
          return boxImage.error()
        }
      }
      
      
      const image = (boxImage && boxImage.moved())?`uploads/boxes/${boxImage.fileName}`:"";

      const name = boxData.name.toLowerCase().trim()

      const box = await Box.findOrCreate({
        "name": name,
        'image':image
      })

      const cateBoxes = await CategoryBoxes.where({
        category_id: params.category
      }).first();
      if(cateBoxes){
        if(!cateBoxes.boxes.includes(box['_id'])){
          cateBoxes.boxes.push(box['_id'])
          cateBoxes.save();           
        }
      }else{
        const cateBoxes = await CategoryBoxes.create({
          "category_id": params.category,
          "boxes": [box['_id']]
        })
      }
      
      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: box
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
      const boxData = request.all()

      /**
       * collect supplier category from request
       */

      const name = boxData.name
      // upload image
      const boxImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(boxImage){

        await boxImage.move(Helpers.publicPath('uploads/boxes'), {
          name: `${new Date().getTime()}.${boxImage.subtype}`
        })          
        
        if (!boxImage.moved()) {
          return boxImage.error()
        }
      }
      
      
      const image = (boxImage && boxImage.moved())?`uploads/boxes/${boxImage.fileName}`:"";
      const box = await Box.where({
        "_id":params['id']
      }).first()
      if(image)
        box.image=image;
      box.name=name;
      box.save();
      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: box
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

module.exports = BoxController
