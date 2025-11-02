export const useToastStore = defineStore("toasts", () => {
  const config = useRuntimeConfig();
  const toasts = ref([]);

  function addToast({ icon, message, type }) {
    const id = Math.floor(Math.random() * 1000);

    if (type !== "error" && type !== "success" && type !== "warning" && type !== "info") {
      throw new Error("Invalid type. Must be either 'error', 'success', 'warning', or 'info'");
    }

    toasts.value.push({ id, icon, message, type });

    setTimeout(() => {
      deleteToast(id);
    }, config.public.toast.dissapearanceTime);
  }

  function deleteToast(id) {
    const parsedId = Number(id);
    toasts.value = toasts.value.filter((toast) => toast.id !== parsedId);
  }

  return { toasts, addToast, deleteToast };
});
