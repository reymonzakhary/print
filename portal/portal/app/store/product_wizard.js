export default {
  state: () => ({
    // General
    activate_wizard: false,
    activate_details: false,
    active_detail: "info",
    wizard_component: "AddProductOverview",
    wizard_type: "",
    // from producer
    selected_producer: "",
    selected_producer_categories: [],
    // from preset || blank
    // search
    search: "",
    //search term
    custom_name: "",
    // custom name for linked value
    selected_search_item: {},
    // selected item from search
    name: {},
    // unique identifier for option

    // selected values
    selected_category: {},
    selected_boops: [],
    selected_box: {},
    selected_option: {},
    selected_divider: "",
    price_collection: [],
  }),
  mutations: {
    // General
    activate_details(state, boolean) {
      state.activate_details = boolean;
    },
    set_active_detail(state, active) {
      state.active_detail = active;
    },
    set_wizard_type(state, type) {
      state.wizard_type = type;
    },
    set_wizard_component(state, component) {
      state.wizard_component = component;
    },
    set_selected_producer(state, producer) {
      state.selected_producer = producer;
    },
    set_selected_producer_categories(state, categories) {
      state.selected_producer_categories = categories;
    },
    set_selected_category(state, category) {
      state.selected_category = category;
    },
    set_selected_boops(state, boops) {
      state.selected_boops = boops;
    },
    set_selected_box(state, box) {
      state.selected_box = box;
    },
    set_selected_option(state, option) {
      state.selected_option = option;
    },
    set_selected_divider(state, divider) {
      state.selected_divider = divider;
    },
    set_price_collection(state, collection) {
      state.price_collection = collection;
    },
    // update
    update_calculation_method(state, method) {
      state.selected_category.calculation_method = method;
    },
    update_price_build(state, build) {
      state.selected_category.price_build = build;
    },
    update_printing_method(state, pm) {
      state.selected_category.printing_method = pm;
    },
    update_ranges(state, ranges) {
      state.selected_category.ranges = ranges;
    },
    update_limits(state, limits) {
      state.selected_category.limits = limits;
    },
    update_range_around(state, range_around) {
      state.selected_category.range_around = range_around;
    },
    update_range_list(state, range_list) {
      state.selected_category.range_list = range_list;
    },
    update_free_entry(state, free_entry) {
      state.selected_category.free_entry = free_entry;
    },
    update_bleed(state, bleed) {
      state.selected_category.bleed = bleed;
    },
    update_delivery_days(state, dlv_days) {
      state.selected_category.dlv_days = dlv_days;
    },
    update_production_days(state, data) {
      state.selected_category.production_days[data.i].active = data.e;
    },
    update_production_dlv(state, data) {
      state.selected_category.production_dlv = data;
    },
    set_selected_boop_multiple_excludes(state, data) {
      let i = null;
      let box = null;
      let idx = null;

      // Use the use_dividers flag to determine lookup strategy
      if (data.use_dividers && state.selected_divider) {
        i = state.selected_boops.findIndex(
          (x) => x.id === data.box_id && x.divider === state.selected_divider,
        );
      } else {
        i = state.selected_boops.findIndex((x) => x.id === data.box_id);
      }

      if (i === -1) {
        return;
      }

      box = state.selected_boops[i];
      idx = box.ops.findIndex((x) => x.id === data.option_id);

      if (idx === -1) {
        return;
      }

      if (state.selected_boops[i].ops[idx].excludes) {
        state.selected_boops[i].ops[idx].excludes.push(data.excludes);
      } else {
        state.selected_boops[i].ops[idx].excludes = [data.excludes];
      }
    },
    set_selected_search_item(state, item) {
      state.selected_search_item = item;
    },
    set_display_name(state, display_name) {
      state.display_name = display_name;
    },
    set_search(state, query) {
      state.search = query;
    },
    set_name(state, name) {
      state.name = name;
    },
  },
  actions: {
    update_selected_category({ state }) {
      const api = useAPI();
      return api
        .put(`categories/${state.selected_category.slug}`, state.selected_category)
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
