const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId;

module.exports = class ValidationHelper {
    /**
     * Validate if string is a valid ObjectId
     */
    static isValidObjectId(id) {
        return mongoose.Types.ObjectId.isValid(id);
    }

    /**
     * Validate required fields
     */
    static validateRequired(body, requiredFields) {
        const missingFields = [];
        
        requiredFields.forEach(field => {
            if (!body[field] || (typeof body[field] === 'string' && body[field].trim() === '')) {
                missingFields.push(field);
            }
        });

        return {
            isValid: missingFields.length === 0,
            missingFields
        };
    }

    /**
     * Validate pagination parameters
     */
    static validatePagination(page, limit) {
        const parsedPage = parseInt(page) || 1;
        const parsedLimit = parseInt(limit) || 10;
        
        return {
            page: Math.max(1, parsedPage),
            limit: Math.min(100, Math.max(1, parsedLimit)), // Max 100 per page
            skip: (Math.max(1, parsedPage) - 1) * Math.min(100, Math.max(1, parsedLimit))
        };
    }

    /**
     * Sanitize string input
     */
    static sanitizeString(str) {
        if (typeof str !== 'string') return str;
        return str.trim();
    }

    /**
     * Validate slug format
     */
    static isValidSlug(slug) {
        const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
        return slugRegex.test(slug);
    }
}