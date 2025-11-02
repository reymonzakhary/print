'use strict'

const SupplierMargin = require('../Model/SupplierMarginModel');

module.exports = class SupplierMarginController {
    /**
     * Removes tenant's catalogues.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */
    static async deleteTenantMargins(request, response)
    {

        try {
            /** @type { object } */

            /** Remove the actual machine */
            await SupplierMargin.deleteMany({'tenant_id': request.params.supplier_id});

            return response.json({
                'message': "tenant Catalogues has been deleted successfully.",
                'status': 200
            })
        } catch (e) {

            return response.json({
                'message': "Error removing catalogue, " + e.message,
                'status': 422
            })
        }
    }
};
