export default {
  state: () => ({
    // Toast settings
    messages: [],
  }),
  mutations: {
    newMessage(state, object) {
      object.top = state.messages.length * 40;
      state.messages.push(object);
    },
    delete(state, index) {
      state.messages.splice(index, 1);
    },
  },
  actions: {
    handle_error({ commit }, error) {
      const { handleError } = useMessageHandler();
      if (error.response) {
        if (error.response._data.errors) {
          for (const message in error.response._data.errors) {
            if (error.response._data.errors.hasOwnProperty(message)) {
              const errmessage = error.response._data.errors[message];
              handleError(errmessage[0]);
              commit("newMessage", {
                text: errmessage[0],
                status: "orange",
              });
            }
          }
        } else {
          handleError(error.response._data.message);
          commit("newMessage", {
            text: error.response._data.message,
            status: "orange",
          });
        }
      } else {
        handleError(error);
        commit("newMessage", {
          text: error,
          status: "orange",
        });
      }
    },
    handle_success({ commit }, response) {
      if (response) {
        if (response.messages) {
          for (const message in response.errors) {
            if (response.messages.hasOwnProperty(message)) {
              const successmessage = response.messages[message];
              commit("newMessage", {
                text: successmessage[0],
                status: "green",
              });
            }
          }
        } else {
          commit("newMessage", {
            text: response.message,
            status: "green",
          });
        }
      } else {
        commit("newMessage", {
          text: response,
          status: "green",
        });
      }
    },
  },
};
