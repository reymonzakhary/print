export default {
  state: () => ({
    // Overview
    categories: [],
    active_category: "",
    loading_boops: false,
    active_items: [],
    workflow: {},
    set_selected_category: {},
    // Details
    collection: "",
    boops: "",
    custom_products: [],
    custom_product_variations: [],
    active_custom_product: {},
    view: "list",
  }),
  mutations: {
    // overview
    set_categories(state, categories) {
      state.categories = categories;
    },
    add_categories(state, categories) {
      for (let i = 0; i < categories.length; i++) {
        const cat = categories[i];
        if (!state.categories.includes(cat)) {
          state.categories.push(cat);
        }
      }
    },
    set_active_category(state, category) {
      state.active_category = category;
    },
    set_selected_category(state, category) {
      state.selected_category = category;
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
    set_shop_workflow(state, flow) {
      if (typeof flow !== "undefined") {
        state.workflow = flow;
      } else {
        state.workflow = {};
      }
    },
    // Details
    set_active_collection(state, collection) {
      state.collection = collection;
    },
    set_boops(state, boops) {
      state.boops = boops;
    },
    set_custom_products(state, products) {
      state.custom_products = products;
    },
    set_view(state, view) {
      state.view = view;
    },
    set_variations(state, variations) {
      state.custom_product_variations = variations;
    },
  },
  actions: {
    async get_custom_categories({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get(`/custom/categories`)
        .then((res) => {
          commit("add_categories", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_custom_category({ commit, dispatch }, slug) {
      const api = useAPI();
      await api
        .get(`/custom/categories/${slug}`)
        .then((res) => {
          commit("set_selected_category", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_categories({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get(`/categories`)
        .then((res) => {
          commit("add_categories", res.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async get_custom_products({ commit, dispatch }, data) {
      await commit("set_custom_products", []);
      const api = useAPI();
      api
        .post(`/shops/categories/${data.cat_id}/products`, {
          type: "custom",
        })
        .then((res) => {
          commit("set_shop_workflow", res.workflow);
          commit("set_custom_products", res.data);
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
