<template>
  <article class="relative">
    <UserFormModal
      v-if="showCustomerFormModal"
      :user="selectedUser"
      @close-modal="showCustomerFormModal = false"
      @create-user="handleCreateUser"
      @update-user="handleUpdateUser"
    />

    <RemoveUserModal
      v-if="showUserRemoveModal"
      @confirm-delete="handleUserDelete"
      @close-modal="showUserRemoveModal = false"
    />

    <div class="sticky top-0 z-30 w-full">
      <UICardHeader class="backdrop-blur">
        <template #left>
          <UICardHeaderTitle :icon="['fal', 'users']" :title="listTitle" />
        </template>

        <template #center>
          <UIInputText
            v-model="filter"
            name="filter"
            :icon="['fal', 'filter']"
            class="mx-auto xl:w-80"
          />
        </template>

        <template #right>
          <UIButton
            v-if="permissions.includes('users-create')"
            :icon="['fad', 'user-plus']"
            class="capitalize"
            @click="showCreateCustomerModal"
          >
            {{ buttonLabel }}
          </UIButton>
        </template>
      </UICardHeader>
      <UICard class="backdrop-blur">
        <div class="flex px-6 py-2 text-xs font-bold uppercase">
          <div class="w-full overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $t("email") }}
          </div>
          <div class="w-full overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $t("created") }}
          </div>
          <div class="w-48 overflow-hidden text-ellipsis whitespace-nowrap">
            <font-awesome-icon :icon="['fad', 'user-check']" />
          </div>
          <div class="w-48" />
        </div>
      </UICard>
    </div>

    <TheUserListSkeleton v-if="isLoading" />
    <TheUserZeroState v-else-if="filtered_users.length === 0" :message="zeroStateMsg" />
    <ul v-else>
      <li v-for="(user, index) in filtered_users" :key="`user_${index}`">
        <UserSingle
          :key="user.id"
          :user="user"
          class="my-2"
          :style="{ 'z-index': filtered_users.length - index }"
          :selected="selectedUser !== null && selectedUser.id === user.id"
          @select-user="selectUser(user)"
          @delete-user="showRemoveCustomerModal"
          @update-user="showUpdateCustomerModal"
          @resend-verification="handleResendVerification"
        />
      </li>
    </ul>
  </article>
</template>

<script>
export default {
  name: "UsersList",
  inject: ["endpoint"],
  props: {
    users: {
      type: Array,
      default: () => [],
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    theFilter: {
      type: String,
      default: "",
    },
  },
  emits: ["user-selected"],
  setup() {
    const authStore = useAuthStore();
    const api = useAPI();
    const { addToast } = useToastStore();
    const { handleError } = useMessageHandler();
    return {
      permissions: authStore.permissions,
      api,
      handleError,
      addToast,
    };
  },
  data() {
    return {
      filter: this.theFilter,
      selectedUser: null,
      removingUserId: null,
      showCustomerFormModal: false,
      showUserRemoveModal: false,
      userID: null,
    };
  },
  computed: {
    filtered_users() {
      const filterText = this.filter.toLowerCase().trim();
      if (filterText === "") return this.users;

      return this.users.filter((user) => {
        return user.email.toLowerCase().includes(filterText);
      });
    },
    zeroStateMsg() {
      if (this.filter !== "") return `${this.$t("No users found with filter:")} ${this.filter}`;
      //prettier-ignore
      return this.$t("Looks like we've hit the data doldrums – no users found in this list!");
    },
    listTitle() {
      if (this.endpoint === "members") return this.$t("Customers");
      return this.$t("Users");
    },
    buttonLabel() {
      if (this.endpoint === "members") return this.$t("create") + " " + this.$t("customer");
      return this.$t("create") + " " + this.$t("user");
    },
  },
  methods: {
    selectUser(user) {
      this.selectedUser = user;
      this.$emit("user-selected", user);
    },
    showUpdateCustomerModal(user) {
      this.selectedUser = user;
      this.showCustomerFormModal = true;
    },
    showCreateCustomerModal() {
      this.selectedUser = null;
      this.showCustomerFormModal = true;
    },
    showRemoveCustomerModal(id) {
      this.removingUserId = id;
      this.showUserRemoveModal = true;
    },
    async handleCreateUser({ user, address }) {
      const userData = {
        email: user.email,
        first_name: user.profile.first_name,
        last_name: user.profile.last_name,
        gender: user.profile.gender,
        roles: user.roles,
        teams: user.teams,
      };
      const ctx_id = user.ctx;

      const newAddress = { ...address };
      if (address.full_name?.length === 0) {
        newAddress.full_name = userData.first_name + " " + userData.last_name;
      }

      try {
        if (!this.userID) {
          const response = await this.api.post(`${this.endpoint}`, userData);
          await this.api.put(`${this.endpoint}/${response.data.id}`, { ctx_id, type: "individual" });
          this.userID = response.data.id;
        }
        await this.createAddress(newAddress);
      } catch (error) {
        this.handleError(error);
      }
    },
    async createAddress(newAddress) {
      try {
        await this.api.post(`${this.endpoint}/${this.userID}/addresses`, newAddress);

        await this.$nextTick();
        this.userID = null;
        this.showCustomerFormModal = false;
      } catch (error) {
        this.handleError(error);
      } finally {
        this.$emit("user-created");
      }
    },
    handleUpdateUser({ user, profile }) {
      Promise.all([
        this.api.put(`${this.endpoint}/${this.selectedUser.id}`, user),
        this.api.put(`users/${this.selectedUser.id}/profile`, profile),
      ])
        .then(() => {
          this.selectedUser = null;
          this.showCustomerFormModal = false;
        })
        .catch((error) => {
          this.selectedUser = null;
          this.showCustomerFormModal = false;
          this.handleError(error);
        })
        .finally(() => {
          this.$nextTick(() => {
            this.$emit("user-updated");
          });
        });
    },
    handleUserDelete() {
      this.api
        .delete(`${this.endpoint}/${this.removingUserId}`)
        .then(() => {
          this.showUserRemoveModal = false;
        })
        .catch((err) => {
          this.showUserRemoveModal = false;
          this.handleError(err);
        })
        .finally(() => {
          this.$emit("user-deleted");
        });
    },
    handleResendVerification(userId) {
      this.api
        .get(`users/${userId}/verification`)
        .then(() => {
          this.addToast({
            message: this.$t("Verification mail sent!"),
            type: "success",
          });
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>
