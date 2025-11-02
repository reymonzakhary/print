export default {
  state: () => ({
    // # all obtained blueprints
    blueprints: {},
    // # single blueprint
    selected_blueprint: {},
    // # blueprint interface
    // main
    editor: "",
    data_to_import: {},
    settings: {},
    // manipulations
    selected_node: {},
    transform: "",
    export_data: "",
    show_settings: false,
  }),
  mutations: {
    // set
    set_editor(state, editor) {
      state.editor = editor;
    },
    select_node(state, node) {
      state.selected_node = node;
    },
    toggle_actionpanel(state, bool) {
      state.show_settings = bool;
    },
    populate_blueprints(state, blueprints) {
      state.blueprints = blueprints;
    },
    add_blueprint(state, blueprint) {
      state.blueprints.push(blueprint);
    },
    remove_blueprint(state, blueprint) {
      let index = state.blueprints.indexOf(blueprint.id);
      state.blueprints.splice(index, 1);
    },
    select_blueprint(state, blueprint) {
      state.selected_blueprint = blueprint;
    },
    update_blueprint_configuration(state, config) {
      state.selected_blueprint.configuration = config;
    },
    populate_settings(state, settings) {
      state.settings = settings;
    },
    populate_import_data(state, data) {
      state.data_to_import = data;
    },
    populate_export_data(state, data) {
      state.export_data = data;
    },
  },
  actions: {
    async delete_blueprint({ commit, dispatch }, { id }) {
      const api = useAPI();
      await api
        .delete(`/blueprints/${id}`)
        .then((response) => {
          commit("remove_blueprint", response);
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
