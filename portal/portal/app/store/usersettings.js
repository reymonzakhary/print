export default {
  state: () => ({
    // Global settings
    global_margins: {},
    me: {},
    settings: [],
    namespace: "",
    namespace_icon: "",
    namespaces: [],
    namespace_areas: [],
    area: "",
    area_active: "",
    area_settings: [],
    filter: "",
    page: 1,
    per_page: 20,
    showResourceIDs: false,
  }),
  getters: {
    me: (state) => state.me,
    showResourceIDs: (state) => state.showResourceIDs,
  },
  mutations: {
    set_component(state, component) {
      state.component = component;
    },
    set_global_margins(state, margins) {
      state.global_margins = margins;
    },
    set_me(state, me) {
      state.me = me;
    },
    set_settings(state, settings) {
      state.settings = settings;
    },
    set_namespace(state, namespace) {
      state.namespace = namespace;
    },
    set_namespace_icon(state, namespace_icon) {
      state.namespace_icon = namespace_icon;
    },
    set_area_active(state, area) {
      state.area_active = area;
    },
    set_page(state, page) {
      state.page = page;
    },
    set_per_page(state, per_page) {
      state.per_page = per_page;
    },
    set_showResourceIDs(state, showResourceIDs) {
      state.showResourceIDs = showResourceIDs;
    },
  },
  actions: {
    async get_settings({ commit }, { namespace, area, sort_by, sort_dir, per_page, page, filter }) {
      const api = useAPI();
      const { handleError } = useMessageHandler();
      try {
        const { data } = await api.get(
          `account/settings?namespace=${namespace ? namespace : ""}&area=${area ? area : ""}&sort_by=${sort_by ? sort_by : "name"}&sort_dir=${sort_dir ? sort_dir : "desc"}&page=${page ? page : ""}&per_page=${per_page ? per_page : 9999999}&search=${filter ? filter : ""}`,
        );
        return data;
      } catch (error) {
        handleError(error);
        throw new Error("Failed to get settings");
      }
    },
    set_showResourceIDs({ commit }, showResourceIDs) {
      if (typeof showResourceIDs !== "boolean") throw error("showResourceIDs must be a boolean");
      commit("set_showResourceIDs", showResourceIDs);
    },
  },
};
