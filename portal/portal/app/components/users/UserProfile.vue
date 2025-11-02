<template>
  <article>
    <EditUserProfileModal
      v-if="showEditUserProfileModal"
      :profile="profile"
      @close-modal="showEditUserProfileModal = false"
      @profile-update="handleUpdateProfile"
    />

    <UICardHeader class="max-h-[42px] flex-nowrap">
      <template #center>
        <UserAvatar
          :is-loading="isLoading"
          :src="!isLoading && permissions.includes('users-profiles-read') && profile.avatar"
          size="80"
          class="relative top-6 z-10 mx-auto"
        />
      </template>
      <template v-if="showEdit" #right>
        <UICardHeaderButton
          v-if="permissions.includes('users-profiles-update')"
          :icon="['fal', 'pencil']"
          :disabled="isLoading"
          class="ml-0"
          @click="handleEditUser"
        />
      </template>
    </UICardHeader>
    <UICard shadow-color="gray-300/50" class="p-2">
      <div class="h-8" />

      <div v-if="hasViewPermission" class="flex flex-col items-center justify-center p-2">
        <div v-if="isLoading" class="loading h-6 w-1/2 rounded bg-gray-200" />
        <h1 v-else class="text-1xl mb-1 font-bold text-gray-800 dark:text-gray-100">
          {{ fullName }}
        </h1>

        <div class="text-sm text-gray-600 dark:text-gray-400">
          <div
            v-if="isLoading"
            class="loading relative top-1 inline-block h-4 w-24 rounded bg-gray-200"
          />
          <span v-else class="text-gray-500">
            <font-awesome-icon :icon="['fal', 'cake-candles']" class="mr-1" />
            {{ dateOfBirth ? dateOfBirth : "??/??/????" }}
          </span>
        </div>

        <!--   Biography     -->
        <div class="w-full p-2 text-center text-sm italic text-gray-600 dark:text-gray-400">
          <SkeletonLine v-if="isLoading" class="mx-auto w-full" />
          <SkeletonLine v-if="isLoading" class="mx-auto w-10/12" />
          <SkeletonLine v-if="isLoading" class="mx-auto w-1/2" />
          <SkeletonLine v-if="isLoading" class="mx-auto w-10/12" />
          <p v-else>{{ profile.bio }}</p>
        </div>
      </div>
      <div v-else class="text-center">You do not have permission to view this user's profile.</div>
    </UICard>
  </article>
</template>

<script>
import moment from "moment";

export default {
  name: "UserProfile",
  inject: ["endpoint"],
  props: ["profile", "userId", "isLoading", "showEdit", "hasViewPermission"],
  emits: ["profile-updated"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const { addToast } = useToastStore();
    const api = useAPI();
    return { permissions, api, addToast };
  },
  data() {
    return {
      showEditUserProfileModal: false,
      moment: moment,
    };
  },
  computed: {
    salutation() {
      if (this.profile.gender === "other") return "Mx.";
      return this.profile.gender === "male" ? "Mr." : "Ms.";
    },
    fullName() {
      const capitalize = (string) => string.charAt(0).toUpperCase() + string.slice(1);
      return `${this.salutation} ${capitalize(this.profile.first_name)} ${this.profile.middle_name ? this.profile.middle_name : ""} ${capitalize(this.profile.last_name)}`;
    },
    dateOfBirth() {
      return this.profile.dob ? this.moment(this.profile.dob).format("DD/MM/YYYY") : "";
    },
    handleError() {
      return (err) => {
        console.error(err);
        this.addToast({
          message: err,
          type: "error",
        });
      };
    },
  },
  methods: {
    handleEditUser() {
      this.showEditUserProfileModal = true;
    },
    handleUpdateProfile(profileData) {
      const theProfile = {
        first_name: profileData.first_name,
        last_name: profileData.last_name,
        dob: profileData.dob,
        bio: profileData.bio,
        gender: this.profile.gender,
      };

      this.api
        .put(`users/${this.userId}/profile`, theProfile)
        .then((response) => {
          this.$emit("profile-updated", response.data);
          this.showEditUserProfileModal = false;
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>
