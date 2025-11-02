export default {
  state: () => ({
    printing_methods: [],
  }),
  mutations: {
    // set
    set_printing_methods(state, methods) {
      state.printing_methods = methods;
    },
    // add
    add_printing_method(state, method) {
      state.printing_methods.push(method);
    },
    // remove
    remove_printing_method(state, method) {
      let index = state.printing_methods.indexOf(method.id);
      state.printing_methods.splice(index, 1);
    },
    // group add resource
    printing_method_update(state, group_id, resource) {
      let index = state.resource_groups.indexOf(group_id);
      // console.log(index);
      state.resource_groups[index] = resource;
    },
  },
  actions: {
    async get_printing_methods({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`printing-methods`)
        .then((response) => {
          commit("set_printing_methods", response.data);
          // this.set_templates(response.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async create_printing_method({ commit, dispatch }, name) {
      const api = useAPI();
      await api
        .post(`printing-methods`, {
          name: name,
        })
        .then((response) => {
          commit("add_printing_method", response.data);
          commit(
            "toast/newMessage",
            {
              status: "green",
              text: response.message ? response.message : `succesfully added ${response.data.name}`,
            },
            {
              root: true,
            },
          );
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async delete_printing_method({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .delete(`printing-methods/${id}`)
        .then((response) => {
          commit("remove_printing_method", {
            id: id,
          });
          commit(
            "toast/newMessage",
            {
              status: "green",
              text: response.message,
            },
            {
              root: true,
            },
          );
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async update_printing_method({ commit, dispatch }, { id, method, type, resource_id }) {
      const api = useAPI();
      await api
        .put(`printing-methods/${id}?${method}=${type}`, {
          resource_id: resource_id,
        })
        .then((response) => {
          commit("printing_method_update", id, response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
