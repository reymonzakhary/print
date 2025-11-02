export default {
  state: () => ({
    active_box: null,
  }),
  mutations: {
    toggle_active_box(state, box) {
      state.active_box = box;
    },
  },
  actions: {},
};
