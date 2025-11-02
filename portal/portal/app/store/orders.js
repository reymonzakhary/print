export default {
  state: () => ({
    // Overview
    orders: [],
    quotations: [],
    statuses: [],
    active_order: null,
    active_order_item: null,
    active_order_data: {},
    active_order_meta: {},
    dont_update: false,
    ordertype: "",
    ordersType: "quotation",
    pagination: { total: 0, page: 1, per_page: 25, last_page: 0 },
    loading: false,
    view: "grid",
    fileList: [],
    fileList_id: null,
    // addresses check
    addressesList: null,
    teams: [],
    editable: true,
  }),
  mutations: {
    // overview
    store(state, orders) {
      state.orders = orders;
    },
    store_quotations(state, quotations) {
      state.quotations = quotations;
    },
    set_teams(state, teams) {
      state.teams = teams;
    },
    set_editable(state, editable) {
      state.editable = editable;
    },
    set_active_order(state, order_id) {
      state.active_order = order_id;
    },
    set_active_order_item(state, item_id) {
      state.active_order_item = item_id;
    },
    set_order(state, order) {
      state.orders.unshift(order);
    },
    set_statuses(state, statuses) {
      state.statuses = statuses;
    },
    // detail
    set_active_order_data(state, orderdata) {
      orderdata.external_connection = true;
      orderdata.external_id = 333;
      state.active_order_data = orderdata;
    },
    update_active_order_data(state, { key, value }) {
      let realValue = value;
      if (key === "type" && typeof value === "boolean") {
        realValue = value ? "order" : "quotation";
      }
      state.active_order_data[key] = realValue;
    },
    set_active_order_meta(state, meta) {
      state.active_order_meta = meta;
    },
    set_active_order_type(state, type) {
      state.ordertype = type;
    },
    set_orders_type(state, type) {
      state.ordersType = type;
    },
    add_service_active_order(state, service) {
      state.active_order_data.services.push(service);
    },
    add_discount_active_order(state, discount) {
      state.active_order_data.discount = discount;
    },
    remove_discount_active_order(state) {
      state.active_order_data.discount = null;
    },
    add_item(state, item) {
      state.active_order_data.items.push(item);
    },
    add_item_media(state, item, id) {
      let i = state.active_order_data.items.findIndex((x) => x.id === id);
      state.active_order_data.items[i].media.push(item);
    },
    update_item(state, { item, id }) {
      let i = state.active_order_data.items.findIndex((x) => x.id === id);
      state.active_order_data.items[i] = item;
    },
    change_item(state, { key, value, id }) {
      let i = state.active_order_data.items.findIndex((x) => x.id === id);
      state.active_order_data.items[i][key] = value;
    },
    delete_item(state, id) {
      let i = state.active_order_data.items.findIndex((x) => x.id === id);
      state.active_order_data.items.splice(i, 1);
    },
    delete_item_media(state, item_id, media_id) {
      let i = state.active_order_data.items.findIndex((x) => x.id === item_id);
      let idx = state.active_order_data.items[i].findIndex((x) => x.id === media_id);
      state.active_order_data.items[i].media[idx].splice(i, 1);
    },
    do_not_update(state, boolean) {
      state.dont_update = boolean;
    },
    set_pagination(state, data) {
      state.pagination.total = data.total;
      state.pagination.page = data.current_page;
      state.pagination.per_page = data.per_page;
      state.pagination.last_page = data.last_page;
    },
    set_loader(state, data) {
      state.loading = data;
    },
    set_view(state, view) {
      state.view = view;
    },
    // update files refresh issue
    change_single_item(state, { key, value, id }) {
      let i = state.active_order_data.items.findIndex((x) => x.id === id);
      if (state.active_order_data.items[i][key] != 0) {
        state.active_order_data.items[i][key].push(value[0]);
      } else {
        state.active_order_data.items[i][key] = value;
      }
    },
    update_fileList(state, { value, id }) {
      state.fileList.push(value[0]);
      state.fileList_id = id;
    },
    delete__single_item(state, id) {
      let i = state.fileList.findIndex((x) => x.id === id);
      state.fileList.splice(i, 1);
    },
    // save addresses list
    set_addresses(state, addresses) {
      state.addressesList = addresses;
    },
  },
  actions: {
    async get_orders({ state, commit, dispatch }) {
      const API = useAPI();
      await API.get(
        `/${state.ordertype}s?include_items=true&per_page=1000&sort_by=date&sort_order=desc`,
      )
        .then((res) => {
          if (state.ordertype === "quotation") {
            commit("store_quotations", res.data);
          } else {
            commit("store", res.data);
          }
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async refreshOrder({ commit, dispatch }, order_id) {
      const API = useAPI();
      await API.get(`orders/${order_id}`)
        .then((res) => {
          commit("set_active_order_data", res.data);
          commit("set_active_order_meta", res.meta);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async refreshQuotation({ commit, dispatch }, order_id) {
      const API = useAPI();
      await API.get(`quotations/${order_id}`)
        .then((res) => {
          commit("set_active_order_data", res.data);
          commit("set_active_order_meta", res.meta);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    //  get order request with quotations
    async refreshQuotations({ commit, dispatch }, order_id) {
      const API = useAPI();
      await API.get(`quotations/${order_id}`)
        .then((res) => {
          commit("set_active_order_data", res.data);
          commit("set_active_order_meta", res.meta);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async update_product({ dispatch }, { ordertype, order_id, item_id, object }) {
      const API = useAPI();
      await API.put(`${ordertype}s/${order_id}/items/${item_id}`, object)
        .then(() => {
          dispatch("get_orders");
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
  },
  getters: {
    order_customer: (state) => {
      return state.active_order_data.customer;
    },
  },
};
