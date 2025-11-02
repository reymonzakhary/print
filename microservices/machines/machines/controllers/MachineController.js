const SupplierMachine = require("../Models/SupplierMachine");
const SupplierOption = require("../Models/SupplierOption");
const SupplierCategory = require("../Models/SupplierCategory");
const StoreMachineRequest = require("../Requests/StoreMachineRequest");
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId

module.exports = class MachineController {

    /**
     * Retrieves the machines and returns them as a JSON response.
     *
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Promise<Object>} - A promise that resolves to the JSON response.
     */
    static async index(request, response) {
        return response.json({
            'data': await SupplierMachine.aggregate([{
                $match: {
                    'tenant_id' : request.params.supplier_id
                },
            },{
                $lookup: {
                    from: "supplier_options",
                    localField: "_id",
                    foreignField: "sheet_runs.machine",
                    as: "options"
                }
            }]),
            "message": 'Machines has been retrieved successfully.',
            "status": 200
        })
    }

    /**
     * Stores a new machine for a specific supplier.
     *
     * @param {Object} request - The request object containing the supplier ID and machine details.
     * @param {Object} response - The response object for sending the result back to the client.
     *
     * @returns {Object} - An object with the machine data, message, and status.
     *                   - If the machine already exists, the result will be an error message.
     *                   - If the machine is created successfully, the result will be the machine data.
     *                   - If an error occurs during the process, the result will be an error message.
     */
    static async store(request, response)
    {
        if(await SupplierMachine.find({
            "tenant_id": request.params.supplier_id,
            "ean": request.body.ean
        }).count()) {
            return response.json({
                'message': 'The machine with ean '+request.body.ean+' has already been taken.',
                'status': 422
            })
        }

        try {
            const machine = await SupplierMachine.create(new StoreMachineRequest().prepare(request.body, request.params.supplier_id));
            return response.json({
                'data': machine,
                "message": 'The machine has been created successfully.',
                "status": 201
            })
        } catch (e) {
            return response.json({
                'message': e.message,
                'status': 422
            })
        }
    }

    /**
     * Updates a machine and its associated options in the supplier database.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {{result: Error}} - The result of the update operation.
     */
    static async update(request, response)
    {

        try {
            /** @const {string}  Update machine */
            let machineId = new ObjectId(request.params.machine);
            await SupplierMachine.find({"_id": machineId, 'tenant_id': request.params.supplier_id}).updateOne(
                request.body.machine
            );

            /** Detach options sheet_runs before attaching the new ones */
            await SupplierOption.updateMany(
                {
                    "sheet_runs.machine": machineId,
                    'tenant_id': request.params.supplier_id,
                },
                {$pull: {
                        "sheet_runs": {"machine": machineId}}
                }
            ).then(res => res)
                .catch(err => console.error(err));


            /**
             * loop options
             */
            for (const op of request.body.options) {
                /** @type {array}  <option>*/
                let sheet_runs = [];
                for (const sh of op.sheet_runs) {

                    if(String(sh.machine) === String(machineId)) {
                        sheet_runs.push({
                            machine: new ObjectId(sh.machine),
                            dlv_production: sh.dlv_production,
                            runs: sh.runs
                        })
                    }
                }

                SupplierOption.updateOne({
                    '_id': new ObjectId(op.id),
                    'tenant_id': request.params.supplier_id,
                }, {
                    $push: {
                        "sheet_runs": sheet_runs[0]
                    }
                }).then(res => res)
                    .catch(err => console.error(err))
            }

            return response.json({
                'message': "Machine has been Updated successfully.",
                'status': 200
            })
        } catch (e) {
            return response.json({
                'message': "Error updating machine" + e,
                'status': 422
            })
        }



    }

    /**
     * Removes a machine from the supplier's inventory.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */
    static async destroy(request, response)
    {

        try {
            /** @type { object } */
            let machineId = new ObjectId(request.params.machine);

            /** Remove all the machines who attached to a supplier category*/
            SupplierCategory.updateMany({
                    additional: {$type: 'array'},
                    tenant_id: request.params.supplier_id

                },
                {
                    $pull: {
                        "additional": { "machine": machineId }
                    }
                })
                .then(res => res)
                .catch(err => console.error(err));

            /** remove all the sheet_runs related to this machine from supplier options */
            await SupplierOption.updateMany(
                {
                    "sheet_runs.machine": machineId,
                    'tenant_id': request.params.supplier_id,
                },
                {$pull: {
                    "sheet_runs": {"machine": machineId}}
                }
            ).then(res => res)
                .catch(err => console.error(err));

            /** Remove the actual machine */
            await SupplierMachine.deleteOne({"_id": machineId, 'tenant_id': request.params.supplier_id});

            return response.json({
                'message': "Machine has been deleted successfully.",
                'status': 200
            })
        } catch (e) {

            console.log(e.message)
            return response.json({
                'message': "Error removing machine" + e,
                'status': 422
            })
        }
    }

}
