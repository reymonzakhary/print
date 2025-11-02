export default {
  state: () => ({
    pagination: {
      total: 0,
      current_page: 1,
      per_page: 100,
      last_page: 0,
    },
    loading: false,
    sort: "",
    sortdir: "asc",
    fired: false,
    fired_start: false,
    endofresults: false,
    waypoint_active: false,
    intersection_options_end: {
      root: null,
      rootMargin: "0px 0px 0px 0px",
      threshold: [0, 1], // [0.25, 0.75] if you want a 25% offset! (was [0, 1])
    },
    // https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
    intersection_options_start: {
      root: null,
      rootMargin: "0px 0px 0px 0px",
      threshold: [1, 0], // [0.25, 0.75] if you want a 25% offset! (was [0, 1])
    }, // https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
  }),
  mutations: {
    set_pagination(state, data) {
      state.pagination.total = data.total;
      state.pagination.current_page = data.current_page;
      state.pagination.per_page = data.per_page;
      state.pagination.last_page = data.last_page;
    },
    set_loader(state, bool) {
      state.loading = bool;
    },
    toggle_fired(state, bool) {
      state.fired = bool;
    },
    toggle_fired_start(state, bool) {
      state.fired_start = bool;
    },
    toggle_endofresults(state, bool) {
      state.endofresults = bool;
    },
    toggle_waypoint_active(state, bool) {
      state.waypoint_active = bool;
    },
  },
};
