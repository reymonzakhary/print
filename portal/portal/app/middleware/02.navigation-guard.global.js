export default defineNuxtRouteMiddleware((to, from) => {
  function useStore() {
    const nuxtApp = useNuxtApp();
    return nuxtApp.$store;
  }

  const store = useStore();

  if (store.state.settings.preventNavigation) {
    const confirmLeave = window.confirm("You have unsaved changes. Do you really want to leave?");
    if (!confirmLeave) {
      return abortNavigation("You have unsaved changes. Do you really want to leave?");
    } else {
      store.commit("settings/setPreventNavigation", false);
    }
  }
});
