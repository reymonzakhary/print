import moment from "moment";
export default {
  state: () => ({
    logged_in: false,
    email: "",
  }),
  mutations: {
    set_logged_in(state, logged_in) {
      state.logged_in = logged_in;
    },
    set_email(state, email) {
      state.email = email;
    },
  },
  actions: {
    async get_user({ commit, dispatch }, route) {
      if (window.sessionStorage.getItem("token")) {
        // retreive token expiration time
        let expires_in = window.sessionStorage.getItem("expires_in");

        // if token is not expired
        if (expires_in && moment(Date.now()).isBefore(moment(expires_in).subtract(1, "minutes"))) {
          // this.$store.commit("authentication/set_logged_in", true);
          // re-authenticate with stored token
          this.$axios.setToken(
            window.sessionStorage.getItem("token"),
            window.sessionStorage.getItem("token_type"),
          );
          // set authenticated flag true
          await this.$axios
            .get("account/me")
            .then((response) => {
              // store authenticated user in store
              commit("settings/set_me", response.data.data, {
                root: true,
              });
              commit("settings/add_tenant_id", response.data.tenant_id, {
                root: true,
              });
              commit("settings/set_language", response.data.meta.language, {
                root: true,
              });

              // store authenticated user in session
              window.sessionStorage.setItem("me", JSON.stringify(response.data.data));

              // retreive the authenticated user's settings
              dispatch(
                "usersettings/get_settings",
                {
                  namespace: "",
                },
                {
                  root: true,
                },
              );
              commit("set_logged_in", true);
              if (route) {
                this.$router.push(route);
                return;
              }
              this.$router.push("/");
            })
            .catch(() => {
              // console.log('get user error ' + error);
              // // clear the session storage for safety purposes
              // window.sessionStorage.clear();

              // // clear axios tokens
              // this.$axios.setToken(false)

              // display error notification for debugging & support purposes
              commit(
                "toast/newMessage",
                {
                  status: "orange",
                  text: "Unauthenticated. Please log in...",
                },
                {
                  root: true,
                },
              );

              // getting authenticated user failed! Not authenticated: logout
              setTimeout(() => {
                dispatch("logout");
              }, 500);
            });
        } else {
          commit(
            "toast/newMessage",
            {
              message: "token expired",
            },
            {
              root: true,
            },
          );
          // request new token
          this.$store.dispatch("authentication/refresh");
        }
      }
    },
    async get_acl_categories({ commit, dispatch }) {
      await this.$axios
        .get(`/acl/categories`)
        .then((res) => {
          commit("product/set_custom_categories", res.data.data, {
            root: true,
          });
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
  },
  getters: {
    expires_in: () => {
      return window.sessionStorage.getItem("expires_in");
    },
    token: () => {
      return window.sessionStorage.getItem("token");
    },
    token_type: () => {
      return window.sessionStorage.getItem("token_type");
    },
  },
};
