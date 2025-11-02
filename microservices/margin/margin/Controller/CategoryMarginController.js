'use strict'

const SupplierMarginModel = require('../Model/SupplierMarginModel');

module.exports = class CategoryMarginController {

    /**
     * Retrieves the margin for a specific category of a supplier.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} Returns a JSON object containing the margin data for the specified category of the supplier.
     *                  If an error occurs, it returns a JSON object with an error message, status code, and error details.
     */
    static async index(request, response) {
        try {
            const {supplier_id, category_slug} = request.params;

            const margin = await SupplierMarginModel.aggregate([
                {$match: {tenant_id: supplier_id}}
            ]);

            let data = {};

            if(margin[0]?.margin?.categories.hasOwnProperty(category_slug)) {
                data = margin[0].margin.categories[category_slug]
            }

            return response.status(200).json(data);


        } catch (error) {
            return response.status(200).json({
                message: 'Margin not found.',
                status: 404,
                error: error.message
            });
        }
    }

    /**
     * Updates the margin for a specific category of a supplier.
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Object} - A JSON object containing the result of the update operation.
     *                    Format: { result: Error }
     *                    If successful, the status will be 200 and a success message will be included.
     *                    If unsuccessful, the status will be 422 and an error message will be included.
     */
    static async update(request, response) {
        try {
            const {supplier_id, category_slug} = request.params;
            const fieldToBeUpdatedPath = `margin.categories.${category_slug}`;

            await SupplierMarginModel.updateOne(
                {tenant_id: supplier_id},
                {$set: {[fieldToBeUpdatedPath]: request.body.margin}},
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
