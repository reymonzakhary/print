import { permissionRegistry } from "./helpers/permissionsRegistry.js";

// Schemas
import quotations from "./schemas/quotations.js";
import printAssortments from "./schemas/printAssortments.js";
import customAssortments from "./schemas/customAssortments.js";
import suppliers from "./schemas/suppliers.js";
import shops from "./schemas/shops.js";
import finder from "./schemas/finder.js";
import members from "./schemas/members.js";
import contexts from "./schemas/contexts.js";
import roles from "./schemas/roles.js";
import acl from "./schemas/acl.js";
import teams from "./schemas/teams.js";

export function usePermissions() {
  const authStore = useAuthStore();
  const userPermissions = computed(() => authStore.permissions || []);

  const permissions = permissionRegistry.resolve(
    quotations,
    printAssortments,
    customAssortments,
    suppliers,
    shops,
    finder,
    members,
    contexts,
    roles,
    acl,
    teams,
  );

  // Check if user has a specific permission
  const hasPermission = (permission) => {
    // Handle permission references
    if (permission && typeof permission === "object" && permission._isPermissionReference) {
      return userPermissions.value.includes(permission._value);
    }

    // Handle direct string permissions
    return userPermissions.value.includes(permission);
  };

  // Check if user has all permissions in array
  const hasAllPermissions = (permissions) => {
    return permissions.every((perm) => hasPermission(perm));
  };

  // Check if user has any permission in array
  const hasAnyPermissions = (permissions) => {
    return permissions.some((perm) => hasPermission(perm));
  };

  // Check if user has access to a permission group
  const hasPermissionGroup = (groupPath) => {
    if (Array.isArray(groupPath)) {
      return checkPermissionGroup(groupPath);
    }

    // Handle full path notation (module.groupName)
    const group = permissions.groups[groupPath];
    if (!group) {
      console.error("Group not found:", groupPath);
      return false;
    }

    return checkPermissionGroup(group);
  };

  // Check if user has access to any of the provided permission groups
  const hasAnyPermissionGroup = (groupPaths) => {
    if (!Array.isArray(groupPaths)) {
      console.error("hasAnyPermissionGroup expects an array of group paths");
      return false;
    }

    return groupPaths.some((groupPath) => hasPermissionGroup(groupPath));
  };

  // Helper to check complex group structures
  const checkPermissionGroup = (group) => {
    // If it's not an array, it's a direct permission
    if (!Array.isArray(group)) {
      return hasPermission(group);
    }

    // If the first item is an array, it's an OR relation
    if (Array.isArray(group[0])) {
      return group.some((subGroup) => {
        return Array.isArray(subGroup)
          ? subGroup.every((perm) => hasPermission(perm))
          : hasPermission(subGroup);
      });
    }

    // Otherwise it's an AND relation
    return group.every((perm) => hasPermission(perm));
  };

  return {
    hasPermission,
    hasAllPermissions,
    hasAnyPermissions,
    hasPermissionGroup,
    hasAnyPermissionGroup,
    permissions,
  };
}
