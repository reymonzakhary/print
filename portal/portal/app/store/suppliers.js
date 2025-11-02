export default {
  state: () => ({
    suppliers: [],
  }),
  mutations: {
    store(state, suppliers) {
      state.suppliers = suppliers;
    },
  },
  getters: {},
  actions: {
    async get_suppliers({ commit }) {
      const api = useAPI();
      await api
        .get("suppliers")
        .then((response) => {
          commit("store", response.data);
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
