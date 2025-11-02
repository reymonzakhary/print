// 2. Navigation Composable (composables/useNavigation.js)
import { navigationItems } from './useNavigationConfig'
export const useNavigation = () => {
  const { hasPermission, hasPermissionGroup, permissions } = usePermissions();
  const route = useRoute();

  const checkPermission = (permission) => {
    if (!permission) return true;

    if (Array.isArray(permission)) {
      return permission.some((p) => hasPermission(p));
    }

    if (permission.includes(".groups.")) {
      const [module, groups, access] = permission.split(".");
      return hasPermissionGroup(permissions[module]?.groups?.[access]);
    }

    return hasPermission(permission);
  };

  const isRouteActive = (item) => {
    if (item.routeMatch) {
      return item.routeMatch(route);
    }
    return route.path.startsWith(item.path);
  };

  const getVisibleItems = (position) => {
    return navigationItems.filter(
      (item) => item.position.includes(position) && checkPermission(item.permission) && permissions,
    );
  };

  const getDisplayName = (item) => {
    return item.displayName || item.name;
  };

  return {
    navigationItems,
    getVisibleItems,
    isRouteActive,
    getDisplayName,
    checkPermission,
  };
};
