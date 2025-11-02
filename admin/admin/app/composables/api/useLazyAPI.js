export const useLazyAPI = (endpoint, options = {}) => {
  const { $api } = useNuxtApp();

  return useLazyFetch(endpoint, {
    $fetch: $api,
    ...options,
  });
};
