'use strict'

const SupplierMargin = require('../Model/SupplierMarginModel');

module.exports = class GeneralMarginController {

    /**
     * Retrieves the margin data for a specific supplier.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} A JSON object containing the margin data, message, and status code.
     *                  If successful, the JSON object will contain the margin data in the 'data' property.
     *                  If an error occurs, the JSON object will contain an error message in the 'message' property,
     *                  the status code in the 'status' property, and the error details in the 'error' property.
     */
    static async index(request, response) {
        try {
            const {supplier_id} = request.params;
            const data = await SupplierMargin.aggregate([
                {$match: {tenant_id: supplier_id}}
            ]);

            return response.status(200).json(data[0]?.margin?.general??[]);

        } catch (error) {
            return response.status(200).json({
                message: 'Margin not found.',
                status: 404,
                error: error.message
            });
        }
    }

    /**
     * Updates the margin value of a supplier.
     *
     * @param {Object} request - The request object containing the supplier ID and the new margin value.
     * @param {Object} response - The response object used to send the status and result of the update operation.
     *
     * @returns {Object} - The result of the update operation.
     * @throws {Error} - If the supplier object is not found in the database.
     */
    static async update(request, response) {
        try {
            const result = await SupplierMargin.updateOne(
                {tenant_id: request.params.supplier_id},
                {$set: {'margin.general': request.body.general}},
                {upsert: true}
            );

            return response.status(200).json({
                message: 'Margin has been updated successfully.',
                status: 200
            });
        } catch (error) {
            return response.status(200).json({
                message: 'Failed to update margin.',
                status: 422,
                error: error.message
            });
        }
    }
};
