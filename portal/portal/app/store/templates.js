export default {
  state: () => ({
    folders: [],
    templates: [],
    chunks: [],
    snippets: [],
    variables: [],
    selected_item: {},
    selected_item_type: "",
  }),
  mutations: {
    set_folders(state, folders) {
      state.folders = folders;
    },
    set_templates(state, templates) {
      state.templates = templates;
    },
    set_chunks(state, chunks) {
      state.chunks = chunks;
    },
    set_snippets(state, snippets) {
      state.snippets = snippets;
    },
    add_template(state, template) {
      state.templates.push(template);
    },
    add_chunk(state, chunk) {
      state.chunks.push(chunk);
    },
    add_folder(state, folder) {
      state.folders.push(folder);
    },
    set_variables(state, variables) {
      state.variables = variables;
    },
    set_selected_item(state, selected_item) {
      state.selected_item = selected_item;
    },
    set_selected_item_type(state, selected_item_type) {
      state.selected_item_type = `${selected_item_type}s`;
    },
    replace_selected_item_content(state, content) {
      state.selected_item.content = content;
    },
  },
  actions: {
    async get_folders({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("modules/cms/folders")
        .then((response) => {
          commit("set_folders", response.data);
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
        .get("modules/cms/templates")
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
        .get(`modules/cms/templates/${id}`)
        .then((response) => {
          commit("set_selected_item", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_chunks({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("modules/cms/chunks")
        .then((response) => {
          commit("set_chunks", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_single_chunk({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`modules/cms/chunks/${id}`)
        .then((response) => {
          commit("set_selected_item", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_snippets({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("modules/cms/snippets")
        .then((response) => {
          commit("set_snippets", response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
