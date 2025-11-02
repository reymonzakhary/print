export default {
  state: () => ({
    roles: [],
    // permissions: [],
  }),
  mutations: {
    set_roles(state, roles) {
      state.roles = roles;
    },
    // set_permissions(state, permission) {
    //    state.permissions.push(permission)
    // },
    clear_permissions(state) {
      state.permissions = [];
    },
  },
  actions: {
    async update_role({ commit, dispatch }, role) {
      const api = useAPI();
      if (role.name === null || role.display_name === null) {
        commit(
          "toast/newMessage",
          {
            text: `please enter the Name & Display name`,
            status: "orange",
          },
          {
            root: true,
          },
        );
      } else {
        await api
          .put(`acl/roles/${role.id}`, role)
          .then((response) => {
            commit(
              "toast/newMessage",
              {
                text: response.message,
                status: "green",
              },
              {
                root: true,
              },
            );
            dispatch("get_roles");
          })
          .catch((error) => {
            dispatch("toast/handle_error", error, {
              root: true,
            });
          });
      }
    },
    async delete_role({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .delete(`roles/${id}`)
        .then((response) => {
          commit(
            "toast/newMessage",
            {
              text: `${response.data.message}`,
              status: "green",
            },
            {
              root: true,
            },
          );
          dispatch("get_roles");
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_roles({ state, commit, dispatch }) {
      const api = useAPI();
      await api
        .get("acl/roles")
        .then((response) => {
          commit("set_roles", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
