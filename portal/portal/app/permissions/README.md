# Permission System Documentation

This document provides a comprehensive guide to the permission system used in our application. The system is designed to be flexible, maintainable, and easy to use.

## Table of Contents

1. [Basic Concepts](#basic-concepts)
2. [Using Permissions in Components](#using-permissions-in-components)
3. [Creating Permission Schemas](#creating-permission-schemas)
4. [Permission Groups](#permission-groups)
5. [Advanced Features](#advanced-features)
6. [Troubleshooting](#troubleshooting)

## Basic Concepts

The permission system is built around a few key concepts:

- **Permissions**: Individual access rights (e.g., `quotations-read`, `print-assortments-categories-list`)
- **Schemas**: Collections of related permissions organized by module
- **Groups**: Logical collections of permissions that represent higher-level access rights
- **References**: Ways to reference permissions or groups within schemas

### Permission Format

Permissions follow a consistent naming pattern:
- `module-action` (e.g., `quotations-read`)
- `module-submodule-action` (e.g., `quotations-items-create`)

Standard actions include:
- `access`: Basic access to the module
- `list`: Ability to view lists/collections
- `read`: Ability to view individual items
- `create`: Ability to create new items
- `update`: Ability to modify existing items
- `delete`: Ability to remove items

## Using Permissions in Components

The permission system is accessed through the `usePermissions` composable:

```javascript
import { usePermissions } from '@/app/permissions/usePermissions';

export default {
  setup() {
    const { permissions, hasPermission, hasAllPermissions, hasAnyPermissions, hasPermissionGroup } = usePermissions();
    
    // Check for a single permission
    const canReadQuotations = hasPermission('quotations-read');
    
    // Check for multiple permissions (AND)
    const canManageQuotations = hasAllPermissions(['quotations-read', 'quotations-create', 'quotations-update']);
    
    // Check for any of multiple permissions (OR)
    const canViewQuotations = hasAnyPermissions(['quotations-read', 'quotations-list']);
    
    // Check for a permission group
    const canAddAssortmentProduct = hasPermissionGroup(permissions.quotations.addAssortmentProduct);
    
    return {
      canReadQuotations,
      canManageQuotations,
      canViewQuotations,
      canAddAssortmentProduct
    };
  }
};
```

### Conditional Rendering

Use permissions to conditionally render UI elements:

```vue
<template>
  <div>
    <h1>Quotations</h1>
    
    <!-- Only show if user has permission -->
    <button v-if="canCreateQuotation">Create New Quotation</button>
    
    <!-- Use v-show for frequently toggled elements -->
    <div v-show="canViewQuotations">
      <quotation-list />
    </div>
  </div>
</template>

<script>
export default {
  setup() {
    const { hasPermission } = usePermissions();
    
    return {
      canCreateQuotation: hasPermission('quotations-create'),
      canViewQuotations: hasPermission('quotations-read')
    };
  }
};
</script>
```

## Creating Permission Schemas

Permission schemas define the structure of permissions for a module. You can create a new schema using the provided script:

```bash
yarn permissions:schema:create my-module --submodules submodule1 submodule2
```

This will create a new schema file at `app/permissions/schemas/myModule.js` with the specified submodules.

### Schema Structure

A basic schema looks like this:

```javascript
import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("my-module", {
  // Define submodules with full CRUD permissions
  submodules: {
    submodule1: true,
    submodule2: true,
  },

  // Define permission groups
  groups: {
    moduleAccess: ["@access", "@list"],
  },
});
```

### Submodule Options

When defining submodules, you have several options:

1. **Full CRUD permissions**:
   ```javascript
   submodules: {
     submodule: true
   }
   ```
   This creates all standard permissions: `my-module-submodule-access`, `my-module-submodule-list`, etc.

2. **Specific actions only**:
   ```javascript
   submodules: {
     submodule: ["access", "list", "read"]
   }
   ```
   This creates only the specified permissions.

3. **Custom actions**:
   ```javascript
   submodules: {
     submodule: {
       actions: ["access", "list"],
       custom: {
         approve: "approve",
         reject: "reject"
       }
     }
   }
   ```
   This creates standard actions plus custom ones like `my-module-submodule-approve`.

## Permission Groups

Permission groups allow you to define logical collections of permissions that represent higher-level access rights.

### Basic Groups

```javascript
groups: {
  moduleAccess: ["@access", "@list"],
  viewOnly: ["@read"]
}
```

### Referencing Permissions

You can reference permissions using the `@` symbol:

- `@action` - References a base permission (e.g., `@read` → `my-module-read`)
- `@submodule-action` - References a submodule permission (e.g., `@items-read` → `my-module-items-read`)

### Cross-Module References

You can reference groups from other modules:

```javascript
groups: {
  addAssortmentProduct: ["@printAssortments.moduleAccess", "@printAssortments.categorySelect"]
}
```

### Complex Group Logic

Groups can represent complex permission requirements:

1. **AND logic** (all permissions required):
   ```javascript
   groups: {
     manageItems: ["@items-read", "@items-create", "@items-update", "@items-delete"]
   }
   ```

2. **OR logic** (any permission set required):
   ```javascript
   groups: {
     addOpenProduct: [
       ["@items-create", "print-assortments-categories-list", "print-assortments-boxes-list"],
       ["@items-create", "custom-assortments-categories-list", "custom-assortments-boxes-list"]
     ]
   }
   ```
   This means the user needs either:
   - `@items-create` AND `print-assortments-categories-list` AND `print-assortments-boxes-list`
   - OR `@items-create` AND `custom-assortments-categories-list` AND `custom-assortments-boxes-list`

## Advanced Features

### Deleting Schemas

To remove a schema, use the provided script:

```bash
yarn permissions:schema:delete my-module
```

This will remove the schema file and update the `usePermissions.js` file.

## Troubleshooting

### Common Issues

1. **Permission not found**: Ensure the permission string matches exactly (case-sensitive, hyphenation)
2. **Group not found**: Check that the group name is correct and the module is registered
3. **Circular references**: Avoid creating circular references between groups

### Debugging

To debug permission issues:

1. Check the user's permissions in the auth store
2. Verify the permission string format
3. Check the console for error messages about unresolved references

### Best Practices

1. Use permission groups for complex permission requirements
2. Keep permission strings consistent and follow the naming convention
3. Document permission requirements for each feature
4. Use descriptive group names that reflect their purpose
