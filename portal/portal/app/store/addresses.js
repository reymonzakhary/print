export default {
  state: () => ({ addresses: [], inputdata: {}, saved: [] }),
  mutations: {
    store(state, addresses) {
      state.addresses = addresses;
    },
    update_filter(state, filter) {
      state.filter = filter;
    },
    add_address(state, address) {
      state.addresses.push(address);
    },
    update_address(state, address) {
      let index = state.addresses.findIndex((x) => x.id === address.id);
      state.addresses[index] = address;
    },
    set_inputdata(state, data) {
      state.inputdata = data;
    },
    set_saved(state, id) {
      state.saved.push(id);
    },
    remove_saved(state, id) {
      state.saved = state.saved.filter((savedId) => savedId !== id);
    },
  },
  getters: {
    addresses: (state) => {
      return state.addresses;
    },
  },
  actions: {
    /**
     * Update the addresses
     * @param {any} user_id
     * @param {any} address_id
     * @param {{ commit: (arg0: string, arg1: any) => void; }} context
     */
    async update({ commit, dispatch }, [address_id, user_id, data]) {
      const api = useAPI();
      await api.put(`users/${user_id.id}/addresses/${address_id}`, data);

      // setTimeout(() => {
      await this.api
        .get(`users/${user_id.id}/addresses/`)
        .then((result) => {
          commit("store", result.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
      // }, 20)
    },
    async delete({ commit, dispatch }, [address_id, user_id]) {
      const api = useAPI();
      await api.delete(`users/${user_id.id}/addresses/${address_id}`);

      // setTimeout(() => {
      await this.api
        .get(`users/${user_id.id}/addresses/`)
        .then((result) => {
          commit("store", result.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
      // }, 20)
    },
    async get({ commit, dispatch }, data) {
      let id;
      let memberFlag = false;
      if (typeof data.user_id === "object") {
        memberFlag = data.user_id.isMember;
        id = data.user_id.id;
      } else {
        id = data.user_id;
      }
      let url = memberFlag ? `members/${id}/addresses` : `users/${id}/addresses`;
      if (data.shop) {
        url += `?shop=${data.shop}`;
      }
      const api = useAPI();
      await api
        .get(url)
        .then((result) => {
          commit("store", result.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
    async getMembers({ commit, dispatch }, user_id) {
      const api = useAPI();
      await api
        .get(`members/${user_id}/addresses/`)
        .then((result) => {
          commit("store", result.data);
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, { root: true });
        });
    },
  },
};
