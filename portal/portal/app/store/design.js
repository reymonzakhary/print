export default {
  state: () => ({
    providers: [],
    templates: [],
    selected_template: {},
    selected_provider: {},
    template_details: {},
    modal_name: "",
  }),
  mutations: {
    set_providers(state, providers) {
      state.providers = providers;
    },
    set_selected_provider(state, provider) {
      state.selected_provider = provider;
    },
    set_templates(state, templates) {
      state.templates = templates;
    },
    set_template_details(state, details) {
      state.template_details = details;
    },
    add_template(state, template) {
      state.templates.push(template);
    },
    set_selected_template(state, template) {
      state.selected_template = template;
    },
    remove_selected_template(state, template) {
      let i = state.templates.findIndex((x) => x.id === template.id);
      state.templates.splice(i, 1);
    },
    set_modal_name(state, name) {
      state.modal_name = name;
    },
  },
  actions: {
    async get_providers({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("design/providers")
        .then((response) => {
          commit("set_providers", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_templates({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("design/provider/templates?per_page=99999")
        .then((response) => {
          commit("set_templates", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_single_template({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`design/provider/templates/${id}`)
        .then((response) => {
          commit("set_template_details", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
