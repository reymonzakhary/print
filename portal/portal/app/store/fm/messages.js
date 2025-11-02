export default {
  state: () => ({
    actionResult: {
      status: null,
      message: null,
    },
    // completing state
    actionProgress: 0,
    // loading spinner
    loading: 0,
    // application error messages
    errors: [],
  }),
  mutations: {
    setActionResult(state, { status, message }) {
      state.actionResult.status = status;
      state.actionResult.message = message;
    },
    clearActionResult(state) {
      state.actionResult.status = null;
      state.actionResult.message = null;
    },
    setProgress(state, progress) {
      state.actionProgress = progress;
    },
    clearProgress(state) {
      state.actionProgress = 0;
    },
    addLoading(state) {
      state.loading += 1;
    },
    subtractLoading(state) {
      state.loading -= 1;
    },
    clearLoading(state) {
      state.loading = 0;
    },
    setError(state, error) {
      state.errors.push(error);
    },
    clearErrors(state) {
      state.errors = [];
    },
  },
  actions: {},
};
