export const useSessionStore = defineStore("session", () => {
  const session = ref(null);
  const initialized = ref(false);

  return {
    session,
    initialized,
  };
});
