export default {
  state: () => ({
    // Overview
    cart: {},
    cart_products: [],
    cart_render: -1,
    cart_flag: "view",
    checkout: false,
    delivery_address: "",
    reference: "",
    progress: [],
  }),
  mutations: {
    // overview
    set_cart(state, cart) {
      state.cart = cart;
    },
    set_cart_products(state, products) {
      state.cart_products = products;
    },
    update_cart_item_status(state, product) {
      let gotya = state.cart_products.find((prod) => console.log(prod));
      // console.log(gotya.st);
      gotya.st = product.output.st;
    },
    set_cart_flag(state, flag) {
      state.cart_flag = flag;
    },
    set_checkout(state, bool) {
      state.checkout = bool;
    },
    set_cart_address(state, address) {
      state.delivery_address = address;
    },
    set_cart_reference(state, reference) {
      state.reference = reference;
    },
    set_cart_render(state, render) {
      state.cart_render = render;
    },
    set_progress(state, progress) {
      let i = null;
      if (progress.active) {
        i = state.progress.findIndex((obj) => obj.active === progress.active);
      } else {
        i = state.progress.findIndex(
          (obj) => obj.active === `${progress.signature}_${progress.product}`,
        );
      }
      if (typeof state.progress[i] !== "undefined") {
        state.progress[i].total = progress.total;
        state.progress[i].current = progress.current;
        state.progress[i].action = progress.action;
        state.progress[i].signature = progress.signature;
        state.progress[i].input = progress.input;
        state.progress[i].product = progress.product;
        if (progress.attempt) {
          state.progress[i].attempt = progress.attempt;
        }
        if (progress.color) {
          state.progress[i].color = progress.color;
        }
      } else {
        state.progress.push({
          active: progress.active ? progress.active : `${progress.signature}_${progress.product}`,
          total: progress.total,
          current: progress.current,
          action: progress.action,
          attempt: progress.attempt,
          signature: progress.signature,
          input: progress.input,
          product: progress.product,
          color: "blue",
        });
      }
    },
    remove_progress(state, id) {
      let i = state.progress.findIndex((obj) => obj.id === id);
      state.progress.splice(i, 1);
    },
  },
  actions: {
    async get_cart({ commit, dispatch }) {
      const api = useAPI();
      await api
        .get("/cart")
        .then((response) => {
          commit("set_cart", response.meta);
          commit("set_cart_products", response.data.products);
        })
        .catch((error) => {
          dispatch("handle_error", error, { root: true });
        });
    },
  },
};
