export default {
  state: () => ({
    tree: [],
    selected_item: {},
    resource: {},
    resource_groups: [],
    recycle_bin: false,
    resource_images: [],
  }),
  mutations: {
    // TREE
    set_tree(state, tree) {
      state.tree = tree;
    },
    set_recycle_bin(state, status) {
      state.recycle_bin = status;
    },
    add_to_tree(state, branch) {
      state.tree.push(branch);
    },
    remove_from_tree(state, branch) {
      let index = state.tree.indexOf(branch.id);
      state.tree.splice(index, 1);
    },
    // RESOURCE
    set_resource(state, resource) {
      state.resource = resource;
    },
    set_selected_item(state, selected_item) {
      state.selected_item = selected_item;
    },
    // RESOURCE GROUP
    // set
    set_resource_groups(state, groups) {
      state.resource_groups = groups;
    },
    // add
    add_resource_group(state, group) {
      state.resource_groups.push(group);
    },
    // remove
    remove_resource_group(state, group) {
      let index = state.resource_groups.indexOf(group.id);
      state.resource_groups.splice(index, 1);
    },
    // group add resource
    resource_group_update(state, group_id, resource) {
      let index = state.resource_groups.indexOf(group_id);
      // console.log(index);
      state.resource_groups[index] = resource;
    },
    // resource_group_add_resource(state, group, resource) {
    //    let index = state.resource_groups.indexOf(group.id)
    //    state.resource_groups[index].resources.push(resource)
    //    console.log(state.resource_groups[index]);
    // },
    // // group remove resource
    // resource_group_remove_resource(state, group) {
    //    let groupindex = state.resource_groups.indexOf(group.id)
    //    let resourceindex = state.resource_groups[groupindex].resources.indexOf(resource.id)
    //    state.resource_groups[groupindex].resources.splice(resourceindex, 1)
    // },

    // images
    set_resource_image(state, image) {
      state.resource_images = [image];
    },
    add_resource_image(state, image) {
      state.resource_images.push(image);
    },
    remove_resource_image(state, image) {
      let index = state.resource_images.indexOf(image);
      state.resource_images.splice(index, 1);
    },
  },
  actions: {
    // TREE
    async get_tree({ commit, dispatch }) {
      const api = useAPI();
      await commit("set_tree", []);
      api
        .get("modules/cms/tree")
        .then((response) => {
          // console.log(response.data.data);
          commit("set_tree", response.data);
          // commit('set_children', response.data.data)
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async get_recycle_bin({ commit, dispatch }) {
      const api = useAPI();
      await commit("set_tree", []);
      api
        .get("modules/cms/tree/trash")
        .then((response) => {
          commit("set_tree", response.data);
          commit("set_recycle_bin", true);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async update_tree({ state, commit, dispatch }, tree) {
      await api
        .put(`modules/cms/tree`, tree)
        .then(() => {
          commit(
            "toast/newMessage",
            { status: "green", text: "shuffled the tree" },
            { root: true },
          );
          dispatch("get_tree");
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    // RESOURCES
    async get_resource({ commit }, id) {
      const api = useAPI();
      await api
        .get(`modules/cms/resources/${id}`)
        .then((response) => {
          commit("set_resource", response.data);
          // this.set_templates(response.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async update_resource({ state, commit, dispatch }) {
      const api = useAPI();
      await api
        .put(`modules/cms/resources/${state.selected_item.id}`, {
          title: state.resource.title,
          long_title: state.resource.long_title,
          menu_title: state.resource.menu_title,
          slug: state.resource.slug,
          description: state.resource.description,
          image: state.resource.image,
          template_id: state.resource.template,
          content: state.resource.content.length === 0 ? null : state.resource.content,
          published: state.resource.published,
          hidden: state.resource.hidden,
        })
        .then((response) => {
          commit("toast/newMessage", { status: "green", text: "saved!" }, { root: true });
          commit("set_resource", response.data);
          dispatch("get_tree");
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    // RECOURSE GROUPS
    async get_resource_groups({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`modules/cms/resources/groups`)
        .then((response) => {
          commit("set_resource_groups", response.data);
          // this.set_templates(response.data.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async create_resource_group({ commit, dispatch }, name) {
      const api = useAPI();
      await api
        .post(`modules/cms/resources/groups`, { name: name })
        .then((response) => {
          commit("add_resource_group", response.data);
          commit(
            "toast/newMessage",
            { status: "green", text: response.data.message },
            { root: true },
          );
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async delete_resource_group({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .delete(`modules/cms/resources/groups/${id}`)
        .then((response) => {
          commit("remove_resource_group", response.data);
          commit(
            "toast/newMessage",
            { status: "green", text: response.data.message },
            { root: true },
          );
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async update_resource_group({ commit, dispatch }, { id, method, type, resource_id }) {
      const api = useAPI();
      await api
        .put(`modules/cms/resources/groups/${id}?${method}=${type}`, { resource_id: resource_id })
        .then((response) => {
          commit("resource_group_update", id, response.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
  },
};
