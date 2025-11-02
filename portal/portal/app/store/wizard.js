export default {
  state: () => ({
    active_step: 1,
  }),
  mutations: {
    set_active_step(state, step) {
      state.active_step = step;
    },
  },
};
