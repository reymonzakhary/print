export const useAPI = (endpoint, options = {}) => {
  const { $api } = useNuxtApp();

  return useFetch(endpoint, {
    $fetch: $api,
    ...options,
  });
};
