export default {
  state: () => ({
    discounts: [],
    runCheck: false,
    priceCheck: false,
  }),
  mutations: {
    /**
     * @param {{ discounts: any; }} state
     * @param {any} discounts
     */
    store(state, discounts) {
      state.discounts = discounts;
    },
    changeRunCheck(state, runCheck) {
      state.runCheck = runCheck;
    },
    changePriceCheck(state, priceCheck) {
      state.priceCheck = priceCheck;
    },
  },
};
