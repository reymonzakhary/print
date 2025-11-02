export default {
  state: () => ({
    // compare tool
    flag: "compare",
    // indicates that categoryOverview is used
    // in compare modus (and not for the add cat wizard)
    compare_categories: [],
    compare_category: {},
    compare_boops: {},
    compare_component: "CategoryOverview",
    compare_options: {},
    compare_qty: [],
    compare_dlv: [0, 10],
    compare_suppliers: [],
    compare_selected_suppliers: [],
  }),
  mutations: {
    // Compare tool
    set_flag(state, flag) {
      state.flag = flag;
    },
    set_compare_component(state, component_name) {
      state.compare_component = component_name;
    },
    set_compare_categories(state, categories) {
      state.compare_categories = categories;
    },
    set_compare_category(state, category) {
      state.compare_category = {
        ...category,
      };
    },
    set_compare_boops(state, boops) {
      state.compare_boops = boops;
    },
    set_compare_suppliers(state, suppliers) {
      state.compare_suppliers = [...suppliers];
    },
    set_compare_qty(state, qty) {
      state.compare_qty = [...qty];
    },
    set_compare_dlv(state, dlv) {
      state.compare_dlv = [...dlv];
    },
    set_selected_suppliers(state, suppliers) {
      state.compare_selected_suppliers = [...suppliers];
    },
    set_compare_options(state, options) {
      state.compare_options = {
        ...options,
      };
    },
  },
};
