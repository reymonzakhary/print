export default useInitializeNuxtApp = () => {
  const { $api } = useNuxtApp();

  return useLazyAsyncData("initializeNuxtApp", async () => {
    const { data } = await $api("/apps");
    return data;
  });
};
