export default {
  state: () => ({
    tags: [],
  }),
  mutations: {
    /**
     *
     * @param {*} state
     * @param {*} tags
     */
    set_tags(state, tags) {
      state.tags = tags;
    },
    /**
     *
     *
     * @param {*} state
     * @param {*} tag
     */
    push_tag(state, tag) {
      if (!state.tags.includes(tag)) {
        state.tags.push(tag);
      }
    },
  },
  actions: {
    /**
     * retrieve the tags
     *
     * @param {*} { commit, dispatch }
     * @param {*} filter
     */
    async get_tags({ commit, dispatch }, filter) {
      const api = useAPI();
      await api
        .get(`tags?search=${filter}`)
        .then((response) => {
          commit("set_tags", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    /**
     * add one for the team!
     *
     * @param {*} { commit, dispatch, state }
     * @param {*} tag
     */
    async add_tag({ commit, dispatch, state }, tag) {
      const api = useAPI();
      if (!state.tags.includes(tag)) {
        await api
          .post("tags", tag)
          .then((response) => {
            commit("push_tag", response.data);
          })
          .catch((error) => {
            dispatch("toast/handle_error", error, {
              root: true,
            });
          });
      }
    },
  },
};
