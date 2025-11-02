export default {
  state: () => ({
    show: false,
    list: {},
  }),
  getters: {},
  mutations: {
    closeHistory(state) {
      state.show = false;
    },
    openHistory(state) {
      state.show = true;
    },
    toggleHistory(state) {
      state.show = !state.show;
    },
    getHistory(state, payload) {
      state.list = payload;
    },
  },
  actions: {
    closeHistory({ commit }) {
      commit("closeHistory");
    },
    openHistory({ commit }) {
      commit("openHistory");
    },
    toggleHistory({ commit }) {
      commit("toggleHistory");
    },
    async getHistory({ commit, rootState, dispatch }, payload) {
      const api = useAPI();
      await api
        .get(`${rootState.orders.ordertype}s/${payload.orderId}/history?page=${payload.page}`)
        .then((res) => {
          commit("getHistory", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
