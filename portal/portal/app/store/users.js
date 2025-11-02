export default {
  state: () => ({
    users: [],
    members: [],
    selected_user: {},
    profile: {},
    filter: "",
    modalName: "",
    modalData: {},
    company: {},
    show_profile: false,
  }),
  mutations: {
    // User specific
    store_users(state, users) {
      state.users = users;
    },
    add_user(state, user) {
      state.users.push(user);
    },
    // member specific
    store_members(state, members) {
      state.members = members;
    },
    add_member(state, member) {
      state.members.push(member);
    },
    // Profile
    set_profile(state, profile) {
      state.profile = profile;
    },
    // selected member/user
    select_user(state, user) {
      state.selected_user = user;
    },
    update_selected_user_roles(state, roles) {
      state.selected_user.roles = roles;
    },
    update_selected_user_teams(state, teams) {
      state.selected_user.teams = teams;
    },
    update_selected_user_ctx(state, ctxs) {
      state.selected_user.ctx = ctxs;
    },
    update_profile(state, profile) {
      state.selected_user.profile = profile;
    },
    // --------------------------
    set_company(state, company) {
      state.company = {
        ...company,
      };
    },
    // general component states
    update_filter(state, filter) {
      state.filter = filter;
    },
    set_modal_name(state, name) {
      state.modalName = name;
    },
    set_modal_data(state, data) {
      state.modalData = data;
    },
    toggle_show_profile(state, bool) {
      state.show_profile = bool;
    },
  },
  getters: {
    // filter_users: (state) => {
    //    return state.allUsers
    //       .filter(user => user.context.slug == 'web')
    //       .filter(user => Object.values(user)
    //          .some(val => val.toString().toLowerCase()
    //             .includes(state.filter.toLowerCase())))
    // },

    // user
    user_exists: (state) => (name) => state.users.some((el) => el.name === name),
    email_exists: (state) => (email) => state.users.some((el) => el.email === email),
    // member
    member_exists: (state) => (name) => state.members.some((el) => el.name === name),
    member_email_exists: (state) => (email) => state.members.some((el) => el.email === email),
  },
  actions: {
    // Users
    async get_users({ commit, dispatch }) {
      const api = useAPI();
      return await api
        .get("users?include_profile=true&per_page=10000")
        .then((response) => {
          // console.log(response);
          commit("store_users", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    /**
     *
     * @param {Number} id
     */
    async get_single_user({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`users/${id}`)
        .then((response) => {
          // console.log(response);
          commit("select_user", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    /**
     *
     * @param {Number} id
     */
    async get_single_user_profile({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`users/${id}/profile`)
        .then((response) => {
          commit("set_profile", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async fetch_user_profile({ commit, dispatch }, id) {
      const api = useAPI();
      const response = await api.get(`users/${id}/profile`);
      return await response.data;
    },
    // members
    async get_members({ commit, dispatch }) {
      const api = useAPI();
      try {
        const response = await api.get("members?include_profile=true&per_page=10000");
        commit("store_members", response.data);
      } catch (error) {
        dispatch("toast/handle_error", error, {
          root: true,
        });
      }
    },
  },
};
