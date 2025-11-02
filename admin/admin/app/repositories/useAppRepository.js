export const useAppRepository = () => {
  const { $api } = useNuxtApp();

  async function index() {
    return $api("/apps");
  }

  return {
    index,
  };
};
