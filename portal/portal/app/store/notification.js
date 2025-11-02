import { v4 as uuidv4 } from "uuid";
export default {
  state: () => ({
    // Notification settings
    notifications: [],
    new_notifications: false,
  }),
  mutations: {
    notifications_from_localstorage(state, array) {
      state.notifications = array;
    },
    set_notification(state, object) {
      const notification = {
        id: uuidv4(),
        ...object,
      };
      state.notifications = [notification, ...state.notifications];
      window.localStorage.setItem("notifications", JSON.stringify(state.notifications));
    },
    set_status(state, bool) {
      state.new_notifications = bool;
    },
    delete_notification(state, notification) {
      let i = state.notifications.findIndex((n) => n === notification);
      state.notifications.splice(i, 1);
      window.localStorage.setItem("notifications", JSON.stringify(state.notifications));
    },
    delete_all_notifications(state) {
      state.notifications = [];
      state.new_notifications = false;
      window.localStorage.setItem("notifications", JSON.stringify(state.notifications));
    },
  },
};
