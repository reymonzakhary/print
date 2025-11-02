export default {
  state: () => ({
    contexts: [],
  }),
  mutations: {
    set_contexts(state, ctx) {
      state.contexts = ctx;
    },
  },
  actions: {
    async get_contexts({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("contexts")
        .then((response) => {
          commit("set_contexts", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
