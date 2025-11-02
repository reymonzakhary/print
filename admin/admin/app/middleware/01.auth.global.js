export default defineNuxtRouteMiddleware(async (to) => {
  const config = useRuntimeConfig();
  const authStore = useAuthStore();

  if (authStore.isAuthenticated) {
    if (to.path === "/auth/login") {
      return navigateTo("/");
    }
  } else {
    if (!config.public.auth.excludedPaths.includes(to.path)) {
      return navigateTo("/auth/login");
    }
  }
});
