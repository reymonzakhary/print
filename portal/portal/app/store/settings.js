export default {
  state: () => ({
    // Global settings
    global_margins: {},
    me: {},
    meta: {},
    info: null,
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
    isUsingTeamAddresses: false,
    showReload: false,
    preventNavigation: false,
  }),
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
    add_tenant_id(state, id) {
      Object.assign(state.me, { tenant_id: id });
    },
    set_meta(state, meta) {
      state.meta = meta;
    },
    set_info(state, info) {
      state.info = info;
    },
    set_settings(state, settings) {
      state.settings = settings;
      if (!settings.data) return;
      const useTeamAddress = settings.data.filter(
        (settings) => settings.key === "use_team_address",
      );
      if (useTeamAddress.length > 0) {
        state.isUsingTeamAddresses = useTeamAddress[0].value;
      }
    },
    update_setting(state, { key, value }) {
      for (let i = 0; i < state.settings.data.length; i++) {
        const setting = state.settings.data[i];
        if (setting.key === key) {
          setting.value = value;
        }
      }
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
    set_show_reload(state, showReload) {
      state.showReload = showReload;
    },
    setPreventNavigation(state, value) {
      state.preventNavigation = value;
    },
  },
  actions: {
    async get_settings(
      { commit, dispatch },
      { namespace, area, sort_by, sort_dir, per_page, page, filter },
    ) {
      const api = useAPI();
      const { handleError } = useMessageHandler();
      try {
        const { data } = await api.get(
          `settings?namespace=${namespace ? namespace : ""}&area=${area ? area : ""}&sort_by=${sort_by ? sort_by : "name"}&sort_dir=${sort_dir ? sort_dir : "desc"}&page=${page ? page : ""}&per_page=${per_page ? per_page : 9999999}&search=${filter ? filter : ""}`,
        );
        commit("set_settings", data);
        return data;
      } catch (error) {
        handleError(error);
        throw new Error("Failed to get settings");
      }
    },
    async get_info({ commit }) {
      const api = useAPI();
      const { handleError } = useMessageHandler();
      try {
        const { data } = await api.get(`info`);
        commit("set_info", data);
        return data;
      } catch (error) {
        handleError(error);
        throw new Error("Failed to get info");
      }
    },
  },
  getters: {
    vat(state) {
      if (state.settings.data) {
        return state.settings.data.find((x) => x.key === "vat")?.value;
      }
    },
    automation(state) {
      return state.meta?.settings?.automation;
    },
    namespaces(state) {
      return state.meta?.modules;
    },
  },
};
