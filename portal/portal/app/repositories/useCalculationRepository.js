export const useCalculationRepository = () => {
  const api = useAPI();

  // const transformCalculationListData = (data) => ({
  //   ...data,
  // });

  const get = async (id, calculationData) => {
    const response = await api.post(`/shops/categories/${id}/products`, calculationData);
    return response.data;
  };

  const getList = async (id, calculationData) => {
    const response = await api.post(`/shops/categories/${id}/products/list`, calculationData);
    return response.data;
  };

  return {
    get,
    getList,
  };
};
