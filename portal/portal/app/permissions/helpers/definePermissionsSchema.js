// Helper function to convert camelCase to kebab-case
const toKebabCase = (str) => {
  if (str === undefined || str === null) {
    return ""; // Handle undefined or null input gracefully
  }
  // Ensure input is a string
  const stringValue = String(str);
  // Match uppercase letters preceded by lowercase letters or digits,
  // or uppercase letters followed by lowercase letters,
  // and insert a hyphen before them. Convert to lowercase.
  return stringValue
    .replace(/([a-z0-9])([A-Z])/g, "$1-$2") // Insert hyphen between camelCase parts
    .replace(/([A-Z])([A-Z][a-z])/g, "$1-$2") // Handle cases like CProducts -> c-products
    .toLowerCase();
};

/**
 * Define a permission schema for a module
 * @param {string} moduleName - The name of the module (e.g., 'quotations')
 * @param {Object} config - Configuration with submodules and groups
 * @returns {Object} - The schema object
 */
export default function definePermissionsSchema(moduleName, config = {}) {
  const kebabModuleName = toKebabCase(moduleName); // Convert module name once

  // Create standard actions for a module
  const createActions = (module, submodule) => {
    const actions = ["access", "list", "create", "read", "update", "delete"];
    const kebabSubmodule = submodule ? toKebabCase(submodule) : null; // Convert submodule name if it exists

    return actions.reduce((result, action) => {
      const permissionString = kebabSubmodule
        ? `${kebabModuleName}-${kebabSubmodule}-${action}` // Use kebab-case parts
        : `${kebabModuleName}-${action}`; // Use kebab-case module name

      // Create a permission reference object
      const permissionRef = {
        toString: () => permissionString,
        valueOf: () => permissionString,
        _isPermissionReference: true,
        _value: permissionString,
      };

      result[action] = permissionRef;
      return result;
    }, {});
  };

  // Initialize schema with base permissions
  const schema = {
    // Base permissions directly on the schema
    ...createActions(kebabModuleName), // Pass kebab-case name

    // Container for groups (will be resolved later)
    groups: {},

    // Internal properties
    _name: moduleName, // Keep original name for registry key
  };

  // Add this reference to enable self-references in group definitions
  schema.this = schema;

  // Process submodules
  if (config.submodules) {
    Object.entries(config.submodules).forEach(([subName, subConfig]) => {
      // Normalize submodule name for property access (e.g., categoryProducts -> categoryProducts)
      // We still use the original subName for object keys if needed,
      // but use kebab-case for the permission string itself.
      const normalizedName = subName; // Keep original for property access if needed, or normalize differently if required
      const kebabSubName = toKebabCase(subName); // Convert for permission string

      if (subConfig === true) {
        // Full CRUD permissions
        schema[normalizedName] = createActions(kebabModuleName, subName); // Pass original subName to createActions
      } else if (Array.isArray(subConfig)) {
        // Specific actions only
        schema[normalizedName] = subConfig.reduce((result, action) => {
          const permissionString = `${kebabModuleName}-${kebabSubName}-${action}`; // Use kebab-case parts

          const permissionRef = {
            toString: () => permissionString,
            valueOf: () => permissionString,
            _isPermissionReference: true,
            _value: permissionString,
          };

          result[action] = permissionRef;
          return result;
        }, {});
      } else if (typeof subConfig === "object") {
        // Object with specified actions and/or custom permissions
        schema[normalizedName] = {};

        if (subConfig.actions) {
          subConfig.actions.forEach((action) => {
            const permissionString = `${kebabModuleName}-${kebabSubName}-${action}`; // Use kebab-case parts

            schema[normalizedName][action] = {
              toString: () => permissionString,
              valueOf: () => permissionString,
              _isPermissionReference: true,
              _value: permissionString,
            };
          });
        }

        if (subConfig.custom) {
          Object.entries(subConfig.custom).forEach(([name, action]) => {
            // Assuming 'action' here represents the final part of the permission string,
            // might need kebab-casing too if it can be camelCase.
            // Let's assume 'action' is already kebab-case or a simple word for now.
            const permissionString = `${kebabModuleName}-${kebabSubName}-${action}`; // Use kebab-case parts

            schema[normalizedName][name] = {
              toString: () => permissionString,
              valueOf: () => permissionString,
              _isPermissionReference: true,
              _value: permissionString,
            };
          });
        }
      }
    });
  }

  // Store unresolved group definitions for later resolution
  if (config.groups) {
    schema.groups = { ...config.groups };
  }

  return schema;
}
