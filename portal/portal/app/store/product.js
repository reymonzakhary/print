export default {
  state: () => ({
    // Overview
    categories: [],
    custom_categories: [],
    fetching_categories: true,
    active_category: "",
    active_custom_category: "",
    loading_boops: false,
    active_items: [],
    assortment_flag: "print_product",
    // Details
    collection: "",
    selection: [],
    boops: "",
    custom_products: [],
    custom_product_variations: [],
    active_custom_product: {},
    view: "columns",
    edit_cat: false,
    component: "",
  }),
  mutations: {
    // overview
    set_component(state, component) {
      state.component = component;
    },
    set_categories(state, categories) {
      state.categories = categories;
    },
    set_custom_categories(state, categories) {
      state.custom_categories = categories;
    },
    add_custom_category(state, category) {
      state.custom_categories.push(category);
    },
    set_fetching_categories(state, boolean) {
      state.fetching_categories = boolean;
    },
    // set_pagination(state, pagination) {
    //    state.pagination = pagination
    // },
    set_active_category(state, category) {
      state.active_category = category;
    },
    set_active_custom_category(state, category) {
      state.active_custom_category = category;
    },
    set_active_custom_product(state, product) {
      state.active_custom_product = product;
    },
    set_loading_boops(state, boolean) {
      state.loading_boops = boolean;
    },
    set_active_items(state, items) {
      state.active_items = items;
    },
    set_assortment_flag(state, flag) {
      state.assortment_flag = flag;
    },
    // Details
    set_active_collection(state, collection) {
      state.collection = collection;
    },
    set_selection(state, selection) {
      state.selection = selection;
    },
    set_boops(state, boops) {
      state.boops = boops;
    },
    set_custom_products(state, products) {
      state.custom_products = products;
    },
    update_custom_product(state, product) {
      let i = state.custom_products.findIndex((p) => p.id === product.id);
      state.custom_products[i] = product;
    },
    remove_custom_product(state, id) {
      let i = state.custom_products.findIndex((p) => p.id === id);
      state.custom_products.splice(i, 1);
    },
    set_view(state, view) {
      state.view = view;
    },
    // Custom prodcuct
    add_variation_box(state, box) {
      if (!state.custom_product_variations.find((b) => b.id === box.id)) {
        state.custom_product_variations.push({
          id: box.id,
          options: [],
          appendage: false,
        });
      }
    },
    remove_variation_box(state, box) {
      let i = state.custom_product_variations.findIndex((b) => b.id === box.id);
      state.custom_product_variations.splice(i, 1);
    },
    add_variation_option(state, { box_id, option }) {
      let i = state.custom_product_variations.findIndex((box) => box.id === box_id);
      if (!state.custom_product_variations[i].options.find((op) => op.id === option.id)) {
        state.custom_product_variations[i].options.push({
          id: option.id,
        });
      }
    },
    add_variation_option_property_validation(state, { box_id, option, validation }) {
      let i = state.custom_product_variations.findIndex((box) => box.id === box_id);
      let idx = state.custom_product_variations[i].options.findIndex((ops) => ops.id === option.id);
      if (!state.custom_product_variations[i].options[idx].properties) {
        Object.assign(state.custom_product_variations[i].options[idx], {
          properties: {
            template: {},
            validations: [],
            props: [],
          },
        });
      }
      state.custom_product_variations[i].options[idx].properties.validations.push(validation);
    },
    add_variation_option_property_template(state, { box_id, option, template }) {
      let i = state.custom_product_variations.findIndex((box) => box.id === box_id);
      let idx = state.custom_product_variations[i].options.findIndex((ops) => ops.id === option.id);
      if (!state.custom_product_variations[i].options[idx].properties) {
        Object.assign(state.custom_product_variations[i].options[idx], {
          properties: {
            template: {},
            validations: [],
            props: [],
          },
        });
      }
      state.custom_product_variations[i].options[idx].properties.template = template;
    },
    remove_variation_option(state, { box_id, option }) {
      let i = state.custom_product_variations.findIndex((box) => box.id === box_id);
      let idx = state.custom_product_variations[i].options?.findIndex((op) => op.id === option.id);
      state.custom_product_variations[i].options.splice(idx, 1);
    },
    set_variations(state, variations) {
      state.custom_product_variations = variations;
    },
    toggle_edit_cat(state, bool) {
      state.edit_cat = bool;
    },
  },
  actions: {
    async get_custom_categories({ commit, dispatch }) {
      commit("set_fetching_categories", true);
      const api = useAPI();
      await api
        .get(`/custom/categories?per_page=9999`)
        .then((res) => {
          commit("set_custom_categories", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        })
        .finally(() => {
          commit("set_fetching_categories", false);
        });
    },
    async get_categories({ commit, dispatch }, page) {
      commit("set_fetching_categories", true);
      const api = useAPI();
      await api
        .get(`/categories?per_page=100&page=${page ? page : 1}`)
        .then((res) => {
          commit("set_categories", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        })
        .finally(() => {
          commit("set_fetching_categories", false);
        });
    },
    async get_custom_products({ commit, dispatch }, data) {
      const api = useAPI();
      await commit("set_custom_products", []);
      api
        .get(
          `/custom/products?category=${data.cat_id}&per_page=100&page=${data.page ? data.page : 1}`,
        )
        .then((res) => {
          commit("set_custom_products", res.data);
          commit("pagination/set_pagination", res.meta, {
            root: true,
          });
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_custom_product({ commit, dispatch }, id) {
      const api = useAPI();
      await api
        .get(`/custom/products/${id}`)
        .then((res) => {
          commit("set_active_custom_product", res.data);
          commit("set_variations", res.data.variations);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
};
