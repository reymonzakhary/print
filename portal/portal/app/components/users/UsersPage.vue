<template>
  <div
    v-if="permissions.includes('users-access')"
    class="flex h-full flex-wrap gap-4 p-4 md:flex-nowrap"
  >
    <div class="w-full md:w-1/2">
      <UsersList
        class="full-height overflow-y-auto"
        :users="membersList"
        :is-loading="isFetchingMembersList"
        @user-selected="handleMemberSelected"
        @user-deleted="fetchAllMembers"
        @user-created="fetchAllMembers"
        @user-updated="fetchAllMembers"
      />
    </div>

    <div id="userInfo" class="w-full md:w-1/4">
      <div>
        <UserProfile
          v-if="
            (permissions.includes('users-profiles-access') && isFetchingMemberProfile) ||
            selectedMember
          "
          :has-view-permission="permissions.includes('users-profiles-read')"
          :user-id="selectedMember?.id"
          :profile="selectedMember?.profile"
          :show-edit="true"
          :is-loading="isFetchingMemberProfile"
          @profile-updated="() => fetchMember(selectedMember.id)"
        />
        <span v-else-if="!permissions.includes('users-profiles-access')">
          {{ $t("You do not have permission to view profiles") }}
        </span>
      </div>

      <div class="mt-4">
        <UserInfo
          v-if="isFetchingMemberProfile || selectedMember"
          :is-loading="isFetchingMemberProfile"
          :user="selectedMember"
          @user-updated="fetchMember(selectedMember.id)"
        />
      </div>
    </div>

    <div class="w-full md:w-1/4">
      <AddressList
        v-if="
          (permissions.includes('users-addresses-access') && isFetchingMemberProfile) ||
          selectedMember
        "
        :user="selectedMember"
        :is-loading="isFetchingMemberProfile"
        :addresses="
          selectedMember ? selectedMember.addresses.filter((address) => !address.team_address) : []
        "
        @address-update="fetchMember(selectedMember.id)"
        @address-delete="fetchMember(selectedMember.id)"
        @address-create="fetchMember(selectedMember.id)"
      />
      <span v-else-if="!permissions.includes('users-addresses-access')">
        {{ $t("You do not have permission to view addresses") }}
      </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  endpoint: {
    type: String,
    required: true,
    validator: (value) => ["members", "users"].includes(value),
  },
});

// Provide endpoint to child components
provide("endpoint", props.endpoint);

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const api = useAPI();
const { handleError } = useMessageHandler();
const { permissions } = storeToRefs(useAuthStore());

// Reactive state
const membersList = ref([]);
const selectedMember = ref(null);
const isFetchingMemberProfile = ref(false);
const isFetchingMembersList = ref(false);

// Computed
const pageTitle = computed(() => {
  if (props.endpoint === "members") {
    return $t("Customers");
  } else if (props.endpoint === "users") {
    return $t("Users");
  }
  return console.error("Invalid endpoint");
});

// Head
useHead({
  title: `${pageTitle.value} | Prindustry Manager`,
});

// Methods
const fetchAllMembers = async () => {
  selectedMember.value = null;
  isFetchingMembersList.value = true;

  try {
    const response = await api.get(`${props.endpoint}?include_profile=true&per_page=10000`);
    membersList.value = response.data;
  } catch (err) {
    handleError(err);
  } finally {
    isFetchingMembersList.value = false;
  }
};

const handleMemberSelected = async (user) => {
  router.push({ query: { id: user.id } });
  await fetchMember(user.id);

  // scroll to user info on mobile
  const container = document.querySelector("#userInfo");
  window.scrollTo({
    top: container.scrollHeight,
  });
};

const fetchMember = async (id) => {
  isFetchingMemberProfile.value = true;
  selectedMember.value = null;

  try {
    const response = await api.get(`${props.endpoint}/${id}?include_profile=true`);
    selectedMember.value = response.data;
  } catch (err) {
    handleError(err);
  } finally {
    isFetchingMemberProfile.value = false;
  }
};

onBeforeMount(() => fetchAllMembers());

onMounted(() => {
  const id = route.query.id;
  if (id && selectedMember.value !== id) handleMemberSelected({ id });
});
</script>

<style scoped>
.full-height {
  height: calc(100vh - 51px - 3rem);
}
</style>
