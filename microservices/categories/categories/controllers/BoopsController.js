const SupplierCategory = require("../Models/SupplierCategory");
const SupplierBoops = require("../Models/SupplierBoops");
const slugify = require('slugify');

const {_processBoxes, _upsertSupplierBoops, validateBoopsExistence} = require('../Helpers/Helper')

module.exports = class BoopsController {
  static async simpleUpdate(request, response) {
    try {
      let supplier_category = await SupplierCategory.findOne(
          {
            tenant_id: request.params.supplier_id,
            slug: request.params.category_slug,
          }
      );

      if (!supplier_category) {
        throw new Error("Supplier does not have this Category");
      }

      await SupplierBoops.deleteMany(
          {
            tenant_id: request.params.supplier_id,
            supplier_category: supplier_category._id,
          }
      );

      await SupplierBoops.create({
        tenant_id: request.params.supplier_id,
        tenant_name: request.body.tenant_name,
        supplier_category: supplier_category._id,
        linked: supplier_category.linked,
        name: supplier_category.name,
        system_key: supplier_category.system_key || slugify(supplier_category.system_key, {lower: true}),
        display_name: supplier_category.display_name,
        slug: supplier_category.slug,
        source_slug: request.body.source_slug || false,
        divided: request.body.divided || false,
        boops: request.body.boops,
      });

      await SupplierCategory.updateOne(
          {
            _id: supplier_category._id
          },
          {
            has_manifest: true
          }
      );

      return response.json({
        data: null,
        message: "BOOPs has bean persisted successfully",
        status: 200
      });
    } catch (exception) {
      return response.json({
        message: exception.message,
        status: 422
      }, 200);
    }
  }

  /**
   * Updates the supplier's category and related entities based on the provided request body.
   *
   * @param {Object} request - The request object containing parameters and body data.
   * @param {Object} response - The response object used to send back the response.
   * @param {Object} request.params - The parameters passed in the request URL.
   * @param {string} request.params.supplier_id - The unique identifier of the supplier.
   * @param {string} request.params.category - The category slug to be updated.
   * @param {Object} request.body - The body of the request containing the new data.
   * @param {string} request.body.tenant_name - The name of the tenant.
   * @param {Array.<Object>} request.body.boops - The list of boxes to be added or updated.
   * @param {string} [request.body.iso] - The ISO standard or code related to the request.
   * @param {boolean} [request.body.divided] - Indicates if the category is divided.
   * @return {Object} - Returns a JSON object containing a message, status, and any additional data.
   */
  static async update(request, response) {
    try {
      const { supplier_id, category } = request.params;
      const { body } = request;

      // 1. Validate supplier category exists
      const supplierCategory = await SupplierCategory.findOne({
        tenant_id: supplier_id,
        slug: category,
      });

      if (!supplierCategory) {
        return response.json({
          message: `Supplier does not have the requested category ${category}`,
          status: 404,
        });
      }

      // 2. Validate input has boxes
      if (!body.boops || body.boops.length === 0) {
        return response.json({
          message: 'Request must include boxes (boops)',
          status: 400,
        });
      }

      // // 3. Validate boxes and options exist
      // const validationErrors = await validateBoopsExistence(body.boops, supplier_id);
      // if (validationErrors.length > 0) {
      //   return response.json({
      //     message: 'Failed to validate boxes and options',
      //     errors: validationErrors,
      //     status: 422
      //   });
      // }
      //
      // // 4. Create or update supplier boops
      // const result = await _upsertSupplierBoops(
      //     supplierCategory,
      //     supplier_id,
      //     body.tenant_name,
      //     body.divided,
      //     body.boops
      // );
        // 3. Process all boxes
        const processedBoxes = await _processBoxes(  // Remove extra parentheses
            body.boops,
            supplier_id,
            body.tenant_name,
            body.iso,
            supplierCategory
        );

        // 4. Create or update supplier boops
        const result = await _upsertSupplierBoops(
            supplierCategory,
            supplier_id,
            body.tenant_name,
            body.divided,
            processedBoxes
        );

      // 5. Mark category as having manifest
      await SupplierCategory.updateOne(
          { _id: supplierCategory._id },
          { has_manifest: true }
      );

      return response.json({
        data: result.data,
        message: result.isNew ? 'Boops created successfully' : 'Boops updated successfully',
        status: result.isNew ? 201 : 200,
      });

    } catch (err) {
      console.error('Error updating supplier boops:', err);
      return response.json({
        message: err.message || 'Internal server error',
        status: 500,
      });
    }
  }

}
