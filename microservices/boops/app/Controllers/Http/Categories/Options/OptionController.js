'use strict'

const Box = use('App/Models/Box')
const Category = use('App/Models/Category')
const BoxOptions = use('App/Models/BoxOptions')
const Option = use('App/Models/Option')
const Helpers = use('Helpers')

const Database = use('Database')
class OptionController {

  /**
   *
   * @param params
   * @param response
   * @returns {Promise<void>}
   */
  async index({ params, response })
  {

    try {

      const boxOption = await BoxOptions.where({
        box_id: params.box
      }).first()
      let options = [];
      if(boxOption) {
        // return response.send(boxOption.options)
        const mongoClient = await Database.connect()
        options = await mongoClient.collection('options')
          .find({
              '_id': {$in:  boxOption.options}
          }).toArray();
        // const boxes = await Box.whereIn('id',boxOption.boxes).fetch()
      
      }
      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: options
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
      let options = await mongoClient.collection('options')
        .find({
            name: eval(`/${match}/i`)
        }).toArray();

        /**
         * return success
         */
        return response.status(200).json({
          status: 'success',
          data: options
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
      
      // check box before store box document
      let box = await Box.where({
                          _id: params.box
                      }).first();
      if(!box)
        return response.status(404).json({
          status: 'success',
          message: 'box not found'
        })

      const optionData = request.all()
      /**
       * collect option boxes from request
       */
      const name = optionData.name.toLowerCase().trim()
      // upload image
      const optionImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(optionImage){

        await optionImage.move(Helpers.publicPath('uploads/options'), {
          name: `${new Date().getTime()}.${optionImage.subtype}`
        })          
        
        if (!optionImage.moved()) {
          return optionImage.error()
        }
      }


      const image = (optionImage && optionImage.moved())?`uploads/options/${optionImage.fileName}`:"";
      const option = await Option.findOrCreate({
        "name": name,
        "image": image,
      })

      const boxOptions = await BoxOptions.where({
        box_id: params.box
      }).first();
      if(boxOptions){
        if(!boxOptions.options.includes(option['_id'])){
          boxOptions.options.push(option['_id'])
          boxOptions.save();           
        }
      }else{
        const boxOptions = await BoxOptions.create({
          "box_id": params.box,
          "options": [option['_id']]
        })
      }
      
      // /**
      //  * return success
      //  */
      return response.status(200).json({
        status: 'success',
        data: option
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
      const optionData = request.all()

      /**
       * collect supplier category from request
       */
      const name = optionData.name
      // upload image
      const optionImage = request.file('image', {
        types: ['image'],
        size: '2mb'
      })
      if(optionImage){

        await optionImage.move(Helpers.publicPath('uploads/options'), {
          name: `${new Date().getTime()}.${optionImage.subtype}`
        })          
        
        if (!optionImage.moved()) {
          return optionImage.error()
        }
      }


      const image = (optionImage && optionImage.moved())?`uploads/options/${optionImage.fileName}`:"";
      const option = await Option.where({
        "_id":params['id']
      }).first()
      if(image)
        option.image=image;
      option.name=name;
      option.save();
      /**
       * return success
       */
      return response.status(200).json({
        status: 'success',
        data: option
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

module.exports = OptionController
