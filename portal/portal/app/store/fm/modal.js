export default {
  state: () => ({
    // modal window
    showModal: false,
    // modal name
    modalName: null,
    // main modal block height
    modalBlockHeight: 0,
  }),
  mutations: {
    setModalState(state, { show, modalName }) {
      state.showModal = show;
      state.modalName = modalName;
    },
    /**
     * Clear modal
     * @param state
     */
    clearModal(state) {
      state.showModal = false;
      state.modalName = null;
    },
    /**
     * Main modal block - set height
     * @param state
     * @param height
     */
    setModalBlockHeight(state, height) {
      state.modalBlockHeight = height;
    },
  },
};
