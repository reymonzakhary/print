<template>
  <UICard
    class="relative flex items-center px-6 text-sm transition-colors duration-75 rounded cursor-pointer hover:bg-theme-50 dark:hover:bg-gray-700"
    :class="{
      'text-theme-900 hover:bg-theme-100 dark:bg-theme-900 font-bold dark:hover:bg-theme-900 !bg-theme-50 border border-theme-100 shadow-none':
        selected,
      'py-3': menuItemsEmpty,
      'py-2': !menuItemsEmpty,
    }"
    @click="$emit('select-user', $event)"
  >
    <div
      class="w-full overflow-hidden whitespace-nowrap text-ellipsis"
      :style="[menuItemsEmpty ? { marginTop: '2px', marginBottom: '2px' } : {}]"
    >
      {{ user.email }}
    </div>
    <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
      {{ moment(user.created_at).format("ddd DD MMM YYYY HH:mm") }}
    </div>
    <div class="w-48">
      <font-awesome-icon :icon="verifiedIcon" :class="verifiedIconColor" />
    </div>
    <div class="w-48 text-right">
      <ItemMenu
        v-if="!menuItemsEmpty"
        :menu-items="menuItems"
        menu-icon="ellipsis-h"
        menu-class="w-8 h-8 rounded-full hover:bg-gray-100"
        dropdown-class="right-0 z-auto font-normal border w-36 dark:border-gray-900"
        @item-clicked="menuItemClicked($event)"
      />
    </div>
  </UICard>
</template>

<script>
import moment from "moment";

export default {
  name: "UserSingle",
  props: {
    user: {
      type: Object,
      required: true,
      validator(value) {
        return (
          Object.prototype.hasOwnProperty.call(value, "id") &&
          Object.prototype.hasOwnProperty.call(value, "email") &&
          Object.prototype.hasOwnProperty.call(value, "created_at")
        );
      },
    },
    selected: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["select-user", "delete-user", "update-user", "resend-verification"],
  setup() {
    const authStore = useAuthStore();
    return {
      permissions: authStore.permissions,
    };
  },
  data() {
    return {
      moment,
      userProfile: null,
    };
  },
  computed: {
    emailIsVerified() {
      return this.user.email_verified_at !== null;
    },
    verifiedIcon() {
      return this.emailIsVerified ? ["fad", "user-check"] : ["fad", "triangle-exclamation"];
    },
    verifiedIconColor() {
      return this.emailIsVerified ? "text-green-700" : "text-orange-700";
    },
    menuItems() {
      return [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit user"),
              classes: "",
              show: this.permissions.includes("users-update"),
            },
            {
              action: "delete",
              icon: "trash-can",
              title: this.$t("delete user"),
              classes: "text-red-500 hover:text-red-600",
              show: this.permissions.includes("users-delete"),
            },
            {
              action: "resendVerification",
              icon: "check",
              title: this.$t("resend verification email"),
              classes: "",
              show: !this.emailIsVerified,
            },
          ],
        },
      ];
    },
    menuItemsEmpty() {
      if (
        !this.permissions.includes("users-delete") &&
        !this.permissions.includes("users-update") &&
        this.emailIsVerified
      ) {
        return true;
      } else {
        return false;
      }
    },
  },
  methods: {
    async menuItemClicked(event) {
      switch (event) {
        case "edit":
          this.$emit("update-user", this.user);
          break;
        case "delete":
          this.$emit("delete-user", this.user.id);
          break;
        case "resendVerification":
          this.$emit("resend-verification", this.user.id);
          break;

        default:
          break;
      }
    },
  },
};
</script>
