'use strict'

const UserMargin = use('App/Models/UserMargin')

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
            const data = request.only([ 'reseller_id', 'user_id', 'margin' ])
            
            /**
             * create margin
             */
            const margin = await UserMargin
                .updateOrCreate(
                    { "reseller_id": data.reseller_id, "user_id": data.user_id },
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
            const data = await UserMargin.where( 'reseller_id', params.reseller_id ).where('user_id', params.user_id).fetch()

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
            const margin = await UserMargin.where('reseller_id', params.reseller_id).where('user_id', params.user_id).first()

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
            return response.status(404).json({
                status: 'error',
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
            const margin = await UserMargin.where('reseller_id', params.reseller_id).where('user_id', params.user_id).first()

            var data = []

            if(margin.margin.categories.hasOwnProperty(params.category_id))
            {
                data = margin.margin.categories[params.category_id]
            }

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
     * update margin
     * 
     * @param {object} ctx.params
     * @param {Request} ctx.request
     * @param {Response} ctx.response
     */
    async update({ params, request, response }) {
        try {
            /**
             * collect data from request
             */
            const marginData = request.only(['margin'])

            /**
             * update margin
             */
            const margin = await UserMargin.query()
                                          .where('_id', params.id)
                                            .update({ $set: marginData})

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
}

module.exports = MarginController
