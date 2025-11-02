const SupplierCatalogue = require("../Models/SupplierCatalogue");
const StoreCatalogueRequest = require("../Requests/StoreCatalogueRequest");
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId

module.exports = class CatalogueController {
    /**
     *
     * @returns {Promise<*>}
     * @param request
     * @param response
     */
    static async index(request, response) {
        return response.json({
            'data': await SupplierCatalogue.aggregate([{
                $match: {
                    'tenant_id' : request.params.supplier_id
                },
            }]),
            "message": 'Machines has been retrieved successfully.',
            "status": 200
        })
    }

    /**
     * Stores a new catalogue for a specific supplier.
     *
     * @param {Object} request - The request object containing the supplier ID and catalogue details.
     * @param {Object} response - The response object for sending the result back to the client.
     *
     * @returns {Object} - An object with the catalogue data, message, and status.
     *                    - If the catalogue already exists, the result will be an error message.
     *                    - If the catalogue is created successfully, the result will be the catalogue data.
     *                    - If an error occurs during the process, the result will be an error message.
     */
    static async store(request, response)
    {
        if(await SupplierCatalogue.find({
            "tenant_id": request.params.supplier_id,
            "ean": request.body.ean
        }).count()) {
            return response.json({
                'message': 'The catalogue with ean '+request.body.ean+' has already been taken.',
                'status': 422
            })
        }

        try {
            const catalogue = await SupplierCatalogue.create(new StoreCatalogueRequest().prepare(request.body, request.params.supplier_id));
            return response.json({
                'data': catalogue,
                "message": 'The Catalogue has been created successfully.',
                "status": 201
            })
        } catch (e) {
            return response.json({
                'message': e,
                'status': 422
            })
        }
    }

    /**
     * Updates a catalogue and its associated options in the supplier database.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - The result of the update operation.
     *
     * @throws {Error} - if an error occurs during the update process.
     */
    static async update(request, response)
    {

        try {
            /** @const {string}  Update catalogue */
            let catalogueId = new ObjectId(request.params.catalogue);
            await SupplierCatalogue.find({"_id": catalogueId, 'tenant_id': request.params.supplier_id}).updateOne(
                request.body
            );

            return response.json({
                'message': "Catalogue has been Updated successfully.",
                'status': 200
            })
        } catch (e) {
            return response.json({
                'message': "Error updating catalogue, " + e.message,
                'status': 422
            })
        }



    }

    /**
     * Removes a catalogue from the supplier's inventory.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */
    static async destroy(request, response)
    {

        try {
            /** @type { object } */
            let catalogueId = new ObjectId(request.params.catalogue);

            /** Remove the actual machine */
            await SupplierCatalogue.deleteOne({"_id": catalogueId, 'tenant_id': request.params.supplier_id});

            return response.json({
                'message': "Catalogue has been deleted successfully.",
                'status': 200
            })
        } catch (e) {

            return response.json({
                'message': "Error removing catalogue, " + e.message,
                'status': 422
            })
        }
    }


    /**
     * Removes tenant's catalogues.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */
    static async deleteTenantCatalogues(request, response)
    {

        try {
            /** @type { object } */

            /** Remove the actual machine */
            await SupplierCatalogue.deleteMany({'tenant_id': request.params.supplier_id});

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

}
