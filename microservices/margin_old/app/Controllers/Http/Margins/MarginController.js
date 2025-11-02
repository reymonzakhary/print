'use strict'

const Margin = use('App/Models/SupplierMargin')

class MarginController {

    /**
     * create margin
     * @param {Request} ctx.request
     * @param {Response} ctx.response
     */
    async store({ request, response }) {
        try {
            /**
             * collect data from request
             */
            const data = request.only([ 'reseller_id', 'margin' ])
            /**
             * create margin
             */
            const margin = await Margin
                .updateOrCreate(
                    { "reseller_id": String(data.reseller_id) },
                    { "margin": data.margin }
                )

            /**
             * return success
             */
            return response.status(200).json({
                status: 'success',
                data: margin
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
     * Show Specified Margin by one param
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async show({response, params})
    {
        try {
            /**
             * define margin data array
             */
            const data = await Margin.where( 'reseller_id', params.reseller_id ).fetch()

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
     * Show Margin - General Only
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async showGeneral({response, params})
    {
        try {
            /**
             * define Margin array
             */
            const margin = await Margin.where('reseller_id', params.reseller_id).first()

            var data = margin.margin.general

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
            return response.status(200).json({
                status: 404,
                message: 'Page not found'
            })
        }
    }

    /**
     * Show Margin - Category Only
     *
     * @param {object} ctx.params
     * @param {Response} ctx.response
     */
    async showCategory({response, params})
    {

        try {
            /**
             * define Margin array
            */
           const margin = await Margin.where('tenant_id', params.tenant_id)
                                      .first()
          let data = {};

          if(margin.margin.categories.hasOwnProperty(params.category_slug)) {
                data = margin.margin.categories[params.category_slug]
            }
            /**
             * return success
             */
            return response.status(200).json(data)
        } catch (e) {
            /**
             * return error
             */
            return response.status(200).json({
              data: [],
              status: '200',
              message: 'No margin found'
            })
        }
    }

    /**
     * update margin
     *
     * @param {object} ctx.params
     * @param {Request} ctx.request
     * @param {Response} ctx.response
     */
    async update({ params, request, response }) {
        /**
         * collect data from request
         */
        let marginData = request.only(['margin'])

        for (let i in marginData.margin ) {
          marginData.margin[i].status = !!parseInt(marginData.margin[i].status)
        }

         /**
         * define Margin array
         */
        const margin = await Margin.where({tenant_id : params.tenant_id }).first()
        let data = [];
        if(margin) {
            if(margin.margin.categories.hasOwnProperty(params.category_slug)) {
                /** update de db */
                if(marginData.margin['0'].slots === undefined) {
                  marginData.margin['0'].slots = []
                }
                if(marginData.margin['1'].slots === undefined) {
                  marginData.margin['1'].slots = []
                }
                margin.margin.categories[params.category_slug] = marginData.margin
            }

          data = {...margin}

        }else{
          let categorySlug = String(params.category_slug);
          let marginToStore = {
            tenant_id:String(params.tenant_id),
            margin:{
              general:[],
              categories:{
                [categorySlug]:marginData.margin
              }
            }
          }
          await Margin.create(marginToStore)
          /**
           * return success
           */
            return response.status(200).json({
              status: 'success',
              message: 'Margin has been added.'
            })
        }

        if(data.length === 0 ){
            margin.margin.categories = {[params.category_slug]: marginData.margin}
            data = {...margin}
            await Margin
              .query()
              .where({'tenant_id':  params.tenant_id}).update({$set: data.$attributes})

            /**
             * return success
             */
            return response.status(200).json({
              status: 'success',
              message: 'Margin has been added.'
            })
        }

        /**
         * update margin
         */
        await Margin
          .query()
          .where({'tenant_id':  params.tenant_id}).update({$set: data.$attributes})
        /**
         * return success
         */
        return response.status(200).json({
            status: 'success',
            message: 'data has been updated.'
        })
    }

  /**
   * update margin
   *
   * @param {object} ctx.params
   * @param {Request} ctx.request
   * @param {Response} ctx.response
   */
  async updateGeneral({ params, request, response }) {
    try {

      /**
       * collect data from request
       */
      const marginData = request.only(['general'])
      /**
       * define Margin array
       */
      const margin = await Margin.where({tenant_id : params.supplier_id }).first()

      if (margin) {

        let data = [];
        /** update de db */
        margin.margin.general = marginData.general
        // delete margin._id
        data =  {...margin}
        /**
         * update margin
         */
        await Margin
          .query()
          .where('tenant_id',  params.supplier_id).update({$set: data.$attributes})
      } else {

        await Margin.create({
          tenant_id:String(params.supplier_id),
          margin:{
            general: marginData.general,
            categories: {}
          }
        })
      }
      /**
       * return success
       */
      return response.status(200).json({
        status: 200,
        message: 'Margin has been updated successfully.'
      })
    } catch (e) {
      /**
       * return error
       */
      return response.status(200).json({
        status: 422,
        message: e
      })
    }
  }
}

module.exports = MarginController
