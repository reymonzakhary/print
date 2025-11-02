export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive("can", {
    mounted(el, binding) {
      const { usePermissions } = useNuxtApp();
      const { hasPermission } = usePermissions();

      if (!hasPermission(binding.value)) {
        el.disabled = true;
      }
    },
  });

  nuxtApp.vueApp.directive("can-function", {
    mounted(el, binding) {
      const { usePermissions } = useNuxtApp();
      const { hasFunctionalAccess } = usePermissions();

      if (!hasFunctionalAccess(binding.value)) {
        el.disabled = true;
      }
    },
  });
});
