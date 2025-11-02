<template>
  <div class="w-screen">
    <SidePanel width="w-3/4">
      <template #side-panel-header>
        <h2 class="p-2 text-sm font-bold tracking-wide uppercase bg-gray-100">
          Select the users
        </h2>

        <transition name="fade">
          <component :is="modalName"></component>
        </transition>
      </template>

      <template #side-panel-content>
        <div
          v-if="permissions.includes('users-access')"
          class="flex flex-wrap lg:flex-no-wrap"
        >
          <section
            class="w-full h-full pr-1 mt-4 overflow-y-auto rounded"
            style="max-height: calc(100vh - 6rem)"
          >
            <div
              class="sticky top-0 flex items-center justify-between p-2 ml-4 text-xs font-bold uppercase rounded-t text-themecontrast-400 bg-theme-400"
            >
              <span class="flex items-center">
                <font-awesome-icon :icon="['fal', 'users']" class="mr-1" />
                {{ $t("users") }}
              </span>

              <div class="relative flex">
                <input
                  ref="filter"
                  v-model="filter"
                  type="text"
                  class="w-full px-2 py-1 text-black bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                  placeholder="filter"
                />
                <font-awesome-icon
                  class="absolute right-0 mt-2 mr-4 text-gray-600"
                  :icon="['fal', 'filter']"
                />
              </div>

              <button
                v-if="permissions.includes('users-create')"
                class="flex items-center px-2 py-1 bg-white rounded-full text-theme-500 hover:bg-theme-100"
                @click="set_modal_name('NewCustomer')"
              >
                <font-awesome-icon :icon="['fad', 'user-plus']" class="mr-1" />
                {{ $t("create") }} {{ type }}
              </button>
            </div>

            <section class="pl-4">
              <div
                class="sticky flex items-center py-1 rounded-b shadow-md shadow-gray-200 dark:shadow-gray-900-md top-10 backdrop-blur-md bg-white/80 dark:bg-gray-900/80"
              >
                <div
                  class="flex-auto px-2 ml-12 overflow-hidden text-xs font-bold tracking-wide uppercase"
                >
                  {{ $t("name") }}
                </div>
                <div
                  class="flex-auto px-2 overflow-hidden text-xs font-bold tracking-wide uppercase"
                >
                  {{ $t("email") }}
                </div>
                <div
                  class="flex-auto hidden px-2 text-xs font-bold tracking-wide uppercase lg:flex"
                >
                  {{ $t("created") }}
                </div>
                <div
                  class="flex-none hidden w-10 px-2 text-xs font-bold tracking-wide text-right uppercase lg:flex"
                >
                  <font-awesome-icon :icon="['fad', 'user-check']" />
                </div>
              </div>

              <template v-if="permissions.includes('users-list')">
                <transition-group name="slide">
                  <template
                    v-for="singleuser in filtered_users"
                    :key="`su_${singleuser.id}`"
                  >
                    <div
                      class="flex items-center my-2 text-sm transition-colors duration-75 bg-white rounded shadow-md cursor-pointer shadow-gray-200 dark:shadow-gray-900 dark:bg-theme-800 hover:bg-theme-100 dark:hover:bg-gray-700"
                      :class="{
                        'text-theme-500 bg-theme-100 hover:bg-theme-100 dark:bg-theme-900 font-bold dark:hover:bg-theme-900':
                          selected_user && selected_user.id === singleuser.id,
                      }"
                      @click="$emit('mediaSourceUser', singleuser)"
                    >
                      <div
                        class="flex-none w-8 h-8 m-2 overflow-hidden rounded-full"
                      >
                        <img
                          v-if="singleuser.profile && singleuser.profile.avatar"
                          class="object-cover w-full h-full"
                          :src="singleuser.profile.avatar"
                        />
                      </div>

                      <div
                        v-if="singleuser.profile"
                        class="flex-auto px-2 overflow-hidden capitalize truncate"
                        :title="singleuser.profile.last_name"
                      >
                        {{ singleuser.profile.first_name }}
                        {{ singleuser.profile.last_name }}
                      </div>

                      <div
                        class="flex-auto px-2 truncate"
                        :title="singleuser.email"
                      >
                        {{ singleuser.email }}
                      </div>

                      <div class="flex-auto hidden px-2 text-xs lg:flex">
                        {{
                          moment(singleuser.created_at).format(
                            "ddd DD MMM YYYY HH:mm",
                          )
                        }}
                      </div>

                      <div
                        v-tooltip="
                          singleuser.email_verified_at !== null
                            ? 'active'
                            : 'email not verified'
                        "
                        class="justify-center flex-none hidden w-10 px-2 py-1 text-right lg:flex"
                      >
                        <font-awesome-icon
                          :icon="[
                            'fad',
                            singleuser.email_verified_at !== null
                              ? 'user-check'
                              : 'triangle-exclamation',
                          ]"
                          :class="
                            singleuser.email_verified_at !== null
                              ? 'text-green-700'
                              : 'text-orange-700'
                          "
                          class="p-1 text-xl bg-white rounded"
                        />
                      </div>

                      <transition name="fade">
                        <component
                          :is="modalName"
                          :user="selected_user"
                          :user_id="
                            selected_user !== null ? selected_user.id : null
                          "
                          :type="type"
                        ></component>
                      </transition>
                    </div>
                  </template>
                </transition-group>
              </template>
              <div v-else class="w-full py-4 italic text-center text-gray-400">
                <font-awesome-icon
                  :icon="['fas', 'do-not-enter']"
                  class="text-yellow-500"
                />
                {{ $t("you are not allowed see") }}
              </div>
            </section>
          </section>

          <transition name="fade">
            <component
              :is="modalName"
              :user="selected_user"
              :user_id="selected_user !== null ? selected_user.id : null"
              :type="type"
            ></component>
          </transition>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
// modal views
import NewCustomer from "~/components/users/modalviews/NewCustomer.vue";
import NewAddress from "~/components/users/modalviews/NewAddress.vue";
import CustomerRemoveModal from "~/components/users/modalviews/CustomerRemoveModal.vue";

// external
import moment from "moment";

import { mapState, mapMutations, mapActions } from "vuex";

export default {
  components: {
    NewCustomer,
    NewAddress,
    CustomerRemoveModal,
  },
  props: {
    type: String,
  },
  emits: ["mediaSourceUser", "on-close"],
  data() {
    return {
      updated_users: [],
      checked: true,
      filter: "",
      moment: moment,
    };
  },
  computed: {
    ...mapState({
      users: (state) => state.users.users,
      modalName: (state) => state.users.modalName,
      selected_user: (state) => state.users.selected_user,
      show_profile: (state) => state.users.show_profile,
    }),
    filtered_users() {
      if (this.filter.length > 0) {
        return this.users.filter((user) => {
          return Object.values(user).some((val) => {
            if (val !== null) {
              return val
                .toString()
                .toLowerCase()
                .includes(this.filter.toLowerCase());
            }
          });
        });
      }
      return this.users;
    },
  },
  watch: {
    users: {
      immediate: true,
      deep: true,
      handler(newValue) {
        return newValue;
      },
    },
  },
  created() {
    if (!this.users.length) {
      this.get_users();
    }
  },
  methods: {
    ...mapMutations({
      store: "users/store",
      set_modal_name: "users/set_modal_name",
      select_user: "users/select_user",
      store_addresses: "addresses/store",
    }),
    ...mapActions({
      get_users: "users/get_users",
      get_roles: "rap/get_roles",
    }),
    close() {
      this.$emit("on-close");
    },
  },
};
</script>

<style lang="scss" scoped>
.scroll-container {
  max-height: calc(50vh - 8rem);
  // overflow-y: scroll !important;
}
.address-scroll-container {
  max-height: calc(100vh - 8rem);
  // overflow-y: scroll !important;
}
</style>
