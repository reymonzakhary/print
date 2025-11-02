export default {
  state: () => ({
    tree: {},
  }),
  mutations: {
    set_tree(state, data) {
      // Object.assign(state.tree, ...tree);
      state.tree = data;
    },
  },
};
