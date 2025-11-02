export const permissionRegistry = {
  groups: {},
  _resolutionStack: [], // Track resolution path to detect circular references

  resolve(...schemas) {
    // Reset groups and resolution stack
    this.groups = {};
    this._resolutionStack = [];

    // Clear existing schema properties before registering new ones
    Object.keys(this).forEach((key) => {
      if (
        key !== "groups" &&
        key !== "resolve" &&
        key !== "_resolveGroupReferences" &&
        key !== "getGroup" &&
        key !== "_resolutionStack" &&
        key !== "_isCircularReference"
      ) {
        delete this[key];
      }
    });

    // Register all schemas first
    schemas.forEach((schema) => {
      this[schema._name] = schema;
    });

    // Then resolve all permission groups
    schemas.forEach((schema) => {
      if (!schema.groups) return;

      Object.entries(schema.groups).forEach(([groupName, groupDef]) => {
        // Reset resolution stack for each top-level resolution
        this._resolutionStack = [];

        const resolvedGroup = this._resolveGroupReferences(groupDef, schema);

        // Update schema's group with resolved values
        schema.groups[groupName] = resolvedGroup;

        // Store in the central registry
        const fullGroupName = `${schema._name}.${groupName}`;
        this.groups[fullGroupName] = resolvedGroup;
      });
    });

    return this;
  },

  _isCircularReference(ref) {
    return this._resolutionStack.includes(ref);
  },

  _resolveGroupReferences(groupDef, schema) {
    // --- Non-array cases first ---

    // Handle string-based references (@module.group, @permission-path)
    if (typeof groupDef === "string") {
      // Cross-schema group reference (@module.group)
      if (groupDef.startsWith("@") && groupDef.includes(".")) {
        const [moduleName, groupName] = groupDef.substring(1).split(".");
        const fullRef = `${moduleName}.${groupName}`;

        // Check for circular references
        if (this._isCircularReference(fullRef)) {
          console.error(`Circular reference detected: ${fullRef}`);
          return []; // Return empty array for circular refs
        }

        // Add to resolution stack
        this._resolutionStack.push(fullRef);

        // Get referenced module
        let referencedModule = this[moduleName];
        let actualModuleName = moduleName;
        if (!referencedModule) {
          const kebabModuleName = moduleName.replace(
            /([A-Z])/g,
            (match) => `-${match.toLowerCase()}`,
          );
          referencedModule = this[kebabModuleName];
          if (referencedModule) {
            actualModuleName = kebabModuleName;
          }
        }

        // Check if module was found
        if (!referencedModule) {
          console.error(
            `Module not found: ${moduleName} (or ${moduleName.replace(/([A-Z])/g, (match) => `-${match.toLowerCase()}`)}) in reference: ${groupDef}`,
          );
          this._resolutionStack.pop();
          return [];
        }

        // Get referenced group definition
        const referencedGroupDef = referencedModule.groups
          ? referencedModule.groups[groupName]
          : undefined;
        if (referencedGroupDef === undefined) {
          console.error(`Group not found: ${groupName} in module: ${actualModuleName}`);
          this._resolutionStack.pop();
          return [];
        }

        // Resolve the referenced group recursively
        const result = this._resolveGroupReferences(referencedGroupDef, referencedModule);

        // Clean up resolution stack
        this._resolutionStack.pop();

        // Return the resolved value (could be primitive or array)
        return result;
      }

      // Self-reference (@permission-path)
      if (groupDef.startsWith("@")) {
        const path = groupDef.substring(1).split("-");
        let current = schema;
        for (const segment of path) {
          if (!current) {
            console.error(
              `Reference path broken at segment '${segment}' for: ${groupDef} in schema: ${schema._name}`,
            );
            return [];
          }
          current = current[segment];
        }

        if (current === undefined || current === null) {
          // More robust check
          console.error(`Reference not found: ${groupDef} in schema: ${schema._name}`);
          return [];
        }

        // Return permission value if it's a permission reference
        if (typeof current === "object" && current._isPermissionReference) {
          return current._value; // Return primitive string
        }

        // Recursively resolve if the target is an array or object (and not null)
        if (typeof current === "object" || Array.isArray(current)) {
          return this._resolveGroupReferences(current, schema);
        }

        // Otherwise, assume it's a primitive value
        return current;
      }

      // If the string doesn't start with @, it's a direct permission string
      return groupDef;
    }

    // Handle permission reference objects
    if (groupDef && typeof groupDef === "object" && groupDef._isPermissionReference) {
      return groupDef._value; // Return the flat permission string
    }

    // --- Handle arrays --- (Moved after primitive/string/ref cases)
    if (Array.isArray(groupDef)) {
      // Detect if this level represents an OR structure (all elements are arrays)
      const isOrGroup = groupDef.length > 0 && groupDef.every((item) => Array.isArray(item));

      if (isOrGroup) {
        // Preserve the nested structure for OR groups
        // Recursively resolve items within each sub-array
        return groupDef.map((subArray) => this._resolveGroupReferences(subArray, schema));
      } else {
        // This is a flat list or contains references that might resolve to arrays.
        // Resolve each item and flatten the results.
        return groupDef.flatMap((item) => {
          const resolvedItem = this._resolveGroupReferences(item, schema);
          // Wrap primitives in an array for flatMap; arrays are handled directly by flatMap.
          return Array.isArray(resolvedItem) ? resolvedItem : [resolvedItem];
        });
      }
    }

    // Handle non-permission-ref objects or other unexpected types defensively
    if (typeof groupDef === "object" && groupDef !== null) {
      console.warn("Unexpected object type encountered during group resolution:", groupDef);
      return []; // Return empty array for unexpected objects
    }

    // Return other primitive types as is (e.g., boolean, number - though unlikely in schema)
    return groupDef;
  },

  getGroup(groupPath) {
    return this.groups[groupPath] || [];
  },
};
