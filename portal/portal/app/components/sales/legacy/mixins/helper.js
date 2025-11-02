import moment from "moment";

import { mapState, mapMutations, mapActions, mapGetters } from "vuex";

export default {
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, permissions };
  },
  data() {
    return {
      endofresults: false,
      reachedBottom: false,
    };
  },
  computed: {
    ...mapState({
      contexts: (state) => state.ctx.contexts,
      orders: (state) => state.orders.orders,
      ordertype: (state) => state.orders.ordertype,
      pagination: (state) => state.orders.pagination,
      loader: (state) => state.orders.loading,
      view: (state) => state.orders.view,
    }),

    ...mapGetters({
      statusColor: "statuses/statusColor",
    }),
  },
  watch: {
    orders: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  methods: {
    ...mapMutations({
      store: "orders/store",
      set_order: "orders/set_order",
      set_active_order_type: "orders/set_active_order_type",
      set_pagination: "orders/set_pagination",
      set_loader: "orders/set_loader",
      set_view: "orders/set_view",
      set_orders_type: "orders/set_orders_type",
    }),

    ...mapActions({
      refresh_orderdata: "orders/get_orders",
      update_product: "orders/update_product",
    }),

    async getOrders(type, gotopage = 1) {
      const api = useAPI();
      const { addToast } = useToastStore();
      const handle_error = (error) => {
        addToast({
          message: error.message,
          type: "error",
        });
      };
      const handle_success = (response) => {
        addToast({
          message: response.message,
          type: "success",
        });
      };
      this.set_active_order_type(type);
      this.endofresults = false;
      this.set_loader(true);

      const url =
        type === "archive"
          ? `orders?include_items=true&per_page=${this.pagination.per_page}&page=${gotopage}&sort_by=date&sort_order=desc&archived=true`
          : `${type}s?include_items=true&per_page=${this.pagination.per_page}&page=${gotopage}&sort_by=date&sort_order=desc`;

      api
        .get(url)
        .then((result) => {
          if (result.message !== null) {
            handle_success(result);
          }
          this.store(result.data);
          // assign pagination info
          if (result.meta) {
            this.set_pagination(result.meta);
          }

          if (this.pagination.page === this.pagination.last_page) {
            this.endofresults = true;
          }

          this.set_orders_type(type);
          this.set_loader(false);
        })
        .catch((error) => handle_error(error));
    },

    async createOrder() {
      const api = useAPI();
      let type = false;
      if (this.ordertype === "order") {
        type = true;
      }
      await api.post(`${this.ordertype}s`, { type: type }).then((response) => {
        if (response.id) {
          this.openDetail(response.id);
        } else {
          this.openDetail(response.data.id);
        }
      });
    },

    async deleteOrder(order) {
      const api = useAPI();
      const { addToast } = useToastStore();
      const handle_error = (error) => {
        addToast({
          message: error.message,
          type: "error",
        });
      };
      const handle_success = (response) => {
        addToast({
          message: response.message,
          type: "success",
        });
      };
      api
        .delete(`/${this.ordertype}s/${order.id}`)
        .then((result) => {
          this.$router.push("/orders"); // push url
          this.loading = false;
          this.refresh_orderdata().then(() => {
            handle_success(result);
          });
        })
        .catch((error) => {
          handle_error(error);
        });
    },
    async openDetail(id) {
      let ordertype = this.ordertype === "archive" ? "order" : this.ordertype;
      var nuxturl = `/orders/order-details?id=${id}&type=${ordertype}`; // build url
      this.$store.commit("orders/set_active_order", id); // set order id as active order
      this.$store.commit("orders/set_active_order_data", {}); // reset store data
      this.$router.push(nuxturl); // push url
    },
    setLocal(view) {
      if (import.meta.client) {
        window.localStorage.setItem("order_view", view);
      }
    },
    moment(datestr) {
      datestr = datestr ? datestr : moment().format("YYYY-MM-DD LTS");
      return moment(datestr, "YYYY-MM-DD LTS").calendar(null, {
        sameDay: "HH:mm",
        lastDay: "[Yesterday]",
        lastWeek: "DD-MM-YYYY",
        sameElse: "DD-MM-YYYY",
      });
    },
  },
};
