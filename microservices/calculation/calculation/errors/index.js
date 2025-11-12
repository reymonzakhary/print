/**
 * Custom Error Classes
 *
 * Provides semantic error types for better error handling and HTTP status mapping.
 */

/**
 * Base Application Error
 */
class ApplicationError extends Error {
    constructor(message, statusCode = 500) {
        super(message);
        this.name = this.constructor.name;
        this.statusCode = statusCode;
        Error.captureStackTrace(this, this.constructor);
    }
}

/**
 * Validation Error (400)
 */
class ValidationError extends ApplicationError {
    constructor(message, field = null) {
        super(message, 400);
        this.field = field;
    }
}

/**
 * Not Found Error (404)
 */
class NotFoundError extends ApplicationError {
    constructor(message) {
        super(message, 404);
    }
}

/**
 * Calculation Error (422)
 */
class CalculationError extends ApplicationError {
    constructor(message, details = null) {
        super(message, 422);
        this.details = details;
    }
}

/**
 * External Service Error (503)
 */
class ExternalServiceError extends ApplicationError {
    constructor(message, service = null) {
        super(message, 503);
        this.service = service;
    }
}

/**
 * Database Error (500)
 */
class DatabaseError extends ApplicationError {
    constructor(message, originalError = null) {
        super(message, 500);
        this.originalError = originalError;
    }
}

module.exports = {
    ApplicationError,
    ValidationError,
    NotFoundError,
    CalculationError,
    ExternalServiceError,
    DatabaseError
};
