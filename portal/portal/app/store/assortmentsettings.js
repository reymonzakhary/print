export default {
  state: () => ({
    // print product
    // semi calculation
    item: {},
    runs: [],
    flag: "",
    show_prod_days: false,
    // Custom products
    active_box: {},
    active_option: {},
    edit: false,
    type: "",
    add_variation: false,
  }),
  mutations: {
    // print product
    // semi calculation
    set_item(state, item) {
      state.item = item;
    },
    update_item(state, { key, value }) {
      if (key === "media") {
        if (typeof value === "string" && value !== "") {
          state.item.media.push(value);
        } else {
          state.item.media.splice(value, 1);
        }
      } else {
        state.item[key] = value;
      }
    },
    set_runs(state, runs) {
      state.runs = runs;
    },
    add_run(state, run) {
      state.runs.push(run);
    },
    update_run(state, index, run) {
      // state.runs[ index ] = run
    },
    add_run_dlv(state, { index, dlv }) {
      if (state.runs[index].dlv_production === undefined) {
        state.runs[index].dlv_production = [];
      }
      state.runs[index].dlv_production.push(dlv);
    },
    update_run_dlv(state, { index, i, dlv }) {
      state.runs[index].dlv_production[i] = dlv;
    },
    set_flag(state, flag) {
      state.flag = flag;
    },
    set_show_prod_days(state, bool) {
      state.show_prod_days = bool;
    },
    // custom product
    set_active_box(state, box) {
      state.active_box = box;
    },
    set_active_option(state, op) {
      state.active_option = op;
    },
    set_edit(state, bool) {
      state.edit = bool;
    },
    set_add_variation(state, bool) {
      state.add_variation = bool;
    },
    set_type(state, type) {
      state.type = type;
    },
  },
  actions: {
    async get_categories({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get(`/categories`)
        .then((res) => {
          commit("set_categories", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
