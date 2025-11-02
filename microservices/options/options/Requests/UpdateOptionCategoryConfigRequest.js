const mongoose = require('mongoose');
const ObjectId = mongoose.Types.ObjectId;

module.exports = class UpdateOptionCategoryConfigRequest {
    prepare(existingConfig, request) {
        return {
            incremental_by: request.incremental_by ?? existingConfig.incremental_by ?? 0,
            dimension: request.dimension ?? existingConfig.dimension ?? "",
            dynamic: request.dynamic ?? existingConfig.dynamic ?? false,
            dynamic_keys: request.dynamic_keys ?? existingConfig.dynamic_keys ?? [],
            start_on: request.start_on ?? existingConfig.start_on ?? 0,
            end_on: request.end_on ?? existingConfig.end_on ?? 0,
            generate: request.generate ?? existingConfig.generate ?? false,
            dynamic_type: request.dynamic_type ?? existingConfig.dynamic_type ?? "integer",
            unit: request.unit ?? existingConfig.unit ?? "mm",
            width: request.width ?? existingConfig.width ?? 0,
            maximum_width: request.maximum_width ?? existingConfig.maximum_width ?? 0,
            minimum_width: request.minimum_width ?? existingConfig.minimum_width ?? 0,
            height: request.height ?? existingConfig.height ?? 0,
            maximum_height: request.maximum_height ?? existingConfig.maximum_height ?? 0,
            minimum_height: request.minimum_height ?? existingConfig.minimum_height ?? 0,
            length: request.length ?? existingConfig.length ?? 0,
            maximum_length: request.maximum_length ?? existingConfig.maximum_length ?? 0,
            minimum_length: request.minimum_length ?? existingConfig.minimum_length ?? 0,
            start_cost: request.start_cost ?? existingConfig.start_cost ?? 0,
            calculation_method: request.calculation_method ?? existingConfig.calculation_method ?? "qty"
        };
    }
}