export default {
  state: () => ({
    margins: [],
    runCheck: false,
    priceCheck: false,
  }),
  mutations: {
    /**
     * @param {{ margins: any; }} state
     * @param {any} margins
     */
    store(state, margins) {
      state.margins = margins;
    },
    changeRunCheck(state, runCheck) {
      state.runCheck = runCheck;
    },
    changePriceCheck(state, priceCheck) {
      state.priceCheck = priceCheck;
    },
  },
};
