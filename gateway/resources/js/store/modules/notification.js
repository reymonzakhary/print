const state = () => ({
   // Notification settings
   messages: []
})

const mutations = {
   newMessage(state, object) {
      object.top = state.messages.length * 40
      state.messages.push(object)
   },
   delete(state, index) {
      state.messages.splice(index, 1)
   },
}

const actions = {
   handle_error({ commit }, error) {
      if (error.response) {
         if (error.response.data.errors) {
            for (const message in error.response.data.errors) {
               if (error.response.data.errors.hasOwnProperty(message)) {
                  const errmessage = error.response.data.errors[message];
                  commit("notification/newMessage", {
                     text: errmessage[0],
                     status: 'red'
                  }, { root: true });
               }
            }
         } else {
            commit("newMessage", {
               text: error.response.data.message,
               status: 'red'
            });
         }
      } else {
         commit("newMessage", {
            text: error,
            status: 'red'
         },);
      }
   },
   handle_success({ commit }, response) {
      if (response) {
         if (response.data.messages) {
            for (const message in response.data.errors) {
               if (response.data.messages.hasOwnProperty(message)) {
                  const successmessage = response.data.messages[message];
                  commit("newMessage", {
                     text: successmessage[0],
                     status: 'green'
                  });
               }
            }
         } else {
            commit("newMessage", {
               text: response.data.message,
               status: 'green'
            });
         }
      } else {
         commit("newMessage", {
            text: response,
            status: 'green'
         });
      }
   }
}

export default {
   namespaced: true,
   state,
   mutations,
   actions
}
