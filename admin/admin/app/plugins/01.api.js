export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();
  const authStore = useAuthStore();

  const api = $fetch.create({
    baseURL: config.public.baseURL,
    headers: {
      Accept: "application/json, text/plain, */*",
    },
    credentials: "include",
    onRequest({ options }) {
      if (authStore.isAuthenticated) {
        options.headers.set("Authorization", `Bearer ${authStore.accessToken}`);
      }
    },
    async onResponseError({ response }) {
      if (response.status === 401) {
        authStore.$reset();
        return nuxtApp.runWithContext(() => navigateTo("/auth/login"));
      }
    },
  });

  return {
    provide: {
      api,
    },
  };
});
