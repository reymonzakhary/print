export default {
  state: () => ({
    teams: [],
    modal_name: "",
  }),
  mutations: {
    // TEAMS
    // set
    set_teams(state, groups) {
      state.teams = groups;
    },
    // add
    add_team(state, group) {
      state.teams.push(group);
    },
    // remove
    remove_team(state, group) {
      let index = state.teams.indexOf(group.id);
      state.teams.splice(index, 1);
    },
    // group add resource
    team_update(state, group_id, resource) {
      let index = state.teams.indexOf(group_id);
      state.teams[index] = resource;
    },
    set_modal_name(state, name) {
      state.modal_name = name;
    },
  },
  actions: {
    // RECOURSE GROUPS
    async get_teams({ commit }, id) {
      const api = useAPI();
      await api.get(`teams?per_page=99999`).then((response) => {
        commit("set_teams", response.data);
      });
    },
    async get_team_addresses({ commit }, id) {
      const api = useAPI();
      await api.get(`/teams/${id}/addresses`).then((response) => {
        commit("addresses/store", response.data, {
          root: true,
        });
      });
    },
    async create_team({ commit, dispatch }, name) {
      const api = useAPI();
      await api
        .post(`teams`, {
          name: name,
        })
        .then((response) => {
          commit("add_team", response.data);
          commit(
            "toast/newMessage",
            {
              status: "green",
              text: response.data.message,
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
    async delete_team({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .delete(`teams/${id}`)
        .then((response) => {
          commit("remove_team", response.data);
          commit(
            "toast/newMessage",
            {
              status: "green",
              text: response.data.message,
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
    async update_team({ commit, dispatch }, { id, method, type, resource_id, name }) {
      await api
        .put(`teams/${id}?${method}=${type}`, {
          resource_id: resource_id,
          name: name,
        })
        .then((response) => {
          commit("team_update", id, response.data);
          dispatch("toast/handle_success", response, {
            root: true,
          });
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
