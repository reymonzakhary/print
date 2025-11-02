export default {
  state: () => ({
    production_days: [],
  }),
  mutations: {
    // set
    set_production_days(state, days) {
      state.production_days = days;
    },
    // add
    add_production_day(state, method) {
      state.production_days.push(method);
    },
    // remove
    remove_production_day(state, slug) {
      let index = state.production_days.find((x) => x.slug === slug);
      state.production_days.splice(index, 1);
    },
    // group add resource
    production_day_update(state, group_id, resource) {
      let index = state.resource_groups.indexOf(group_id);
      // console.log(index);
      state.resource_groups[index] = resource;
    },
  },
  actions: {
    async get_production_days({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get(`delivery-day`)
        .then((response) => {
          commit("set_production_days", response.data);
          // this.set_templates(response.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async create_production_day({ commit, dispatch }, { name, days, mode, price }) {
      const api = useAPI();
      await api
        .post(`delivery-day`, {
          label: name,
          days: days,
          mode: mode,
          price: price,
        })
        .then((response) => {
          commit("add_production_day", response.data);
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
    async delete_production_day({ commit, dispatch }, slug) {
      const api = useAPI();
      await api
        .delete(`delivery-day/${slug}`)
        .then((response) => {
          commit("remove_production_day", slug);
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
    async update_production_day({ commit, dispatch }, { id, method, type, resource_id }) {
      const api = useAPI();
      await api
        .put(`delivery-days/${id}?${method}=${type}`, {
          resource_id: resource_id,
        })
        .then((response) => {
          commit("production_day_update", id, response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
