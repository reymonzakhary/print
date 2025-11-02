<template>
  <div ref="gridContainer" class="grid h-full grid-cols-12 gap-4">
    <div class="col-span-4 h-full overflow-hidden">
      <UICard ref="membersList" class="flex h-fit flex-col" rounded-full>
        <UICardHeader>
          <template #left>
            <UICardHeaderTitle :icon="['fas', 'users']" :title="$t('Team Members')" />
          </template>
          <template #right>
            <button
              v-if="selectedUsers.length > 1 && permissions.includes('members-delete')"
              v-tooltip="$t('Remove selected')"
              class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
              @click.stop="handleRemoveUsers"
            >
              <font-awesome-icon icon="link-slash" />
            </button>
          </template>
        </UICardHeader>
        <div v-if="loading">
          <SkeletonLine v-for="i in 3" :key="i" class="my-1 h-20" />
        </div>
        <div v-else-if="error" class="px-4 py-6 text-center">
          <div class="text-center text-gray-400">
            <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="fa-2x mb-2" />
            <p>{{ $t("An error occurred while fetching the team members") }}</p>
          </div>
        </div>
        <div v-else-if="teamMembers.length === 0" class="px-4 py-6 text-center">
          <div class="text-center text-gray-400">
            <font-awesome-icon :icon="['fal', 'users-slash']" class="fa-2x mb-2" />
            <p>{{ $t("No team members found") }}</p>
          </div>
        </div>
        <div v-else class="flex-1 overflow-y-auto">
          <ul>
            <li v-for="user in teamMembers" :key="user.id">
              <button
                class="flex w-full items-center justify-between border-b border-b-gray-200 p-4 px-2 text-left"
                :class="{
                  'bg-theme-50 text-theme-800':
                    selectedUsers.find((item) => item.id === user.id) ||
                    selectedUser?.id === user.id,
                  'hover:bg-gray-50':
                    !selectedUsers.find((item) => item.id === user.id) &&
                    selectedUser?.id !== user.id,
                }"
                @click="
                  selectedUser = user;
                  selectedUsers = [];
                "
              >
                <div>
                  <input
                    v-if="permissions.includes('members-delete')"
                    type="checkbox"
                    class="me-2"
                    :checked="selectedUsers.find((item) => item.id === user.id)"
                    @click.stop
                    @change="toggleUser(user)"
                  />
                  <span>{{ user.email }}</span>
                </div>
                <button
                  v-if="selectedUsers.length < 2 && permissions.includes('members-delete')"
                  v-tooltip="$t('Remove')"
                  class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
                  @click.stop="handleRemoveUser(user.id, user.type)"
                >
                  <font-awesome-icon icon="link-slash" />
                </button>
              </button>
            </li>
          </ul>
        </div>
      </UICard>
    </div>
    <div class="col-span-8 flex-1 overflow-hidden pb-2">
      <UserProfile
        v-if="selectedUser && selectedUsers.length < 2"
        :show-edit="false"
        type=""
        :user_id="selectedUser.id"
        :profile="selectedUserProfile"
        :has-view-permission="true"
        :is-loading="profileLoading"
        class="w-full"
      />
    </div>
  </div>
</template>

<script setup>
const route = useRoute();
/**
 * Necessary for the UserProfile component
 * Should preferably be refactored to the
 * current standards. My mistake... :P
 */
provide("endpoint", "users");

const { t: $t } = useI18n();

const api = useAPI();
const { confirm } = useConfirmation();
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const teamRepository = useTeamRepository();
const { permissions } = storeToRefs(useAuthStore());

const error = ref(null);

const activeTeam = computed(() => route.params.activeTeam);
const loading = ref(true);
const team = ref(null);

const gridContainer = ref(null);
const membersList = ref(null);
const teamMembers = ref([]);
const selectedUser = ref(null);
const selectedUsers = ref([]);
const selectedUserProfile = ref(null);
const profileLoading = ref(false);

const toggleUser = (user) => {
  const index = selectedUsers.value.findIndex((item) => item.id === user.id);
  if (index > -1) {
    selectedUsers.value.splice(index, 1);
  } else {
    selectedUsers.value.push(user);
  }
  if (selectedUsers.value.length < 2) {
    selectedUser.value = selectedUsers.value[0];
  } else {
    selectedUser.value = null;
  }
};

async function fetchTeam() {
  try {
    error.value = null;
    team.value = await teamRepository.getTeamById(activeTeam.value);
  } catch (error) {
    handleError(error);
    error.value = error;
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await fetchTeam();
});

async function fetchUserProfile(userId) {
  try {
    profileLoading.value = true;
    const { data } = await api.get(`users/${userId}/profile`);
    selectedUserProfile.value = data;
    profileLoading.value = false;
  } catch (error) {
    handleError(error);
  }
}

async function handleRemoveUser(id, type) {
  try {
    await confirm({
      title: $t("remove user from team"),
      message: $t("Are you sure you want to remove this user from this team?"),
      confirmOptions: {
        label: $t("remove"),
        variant: "danger",
      },
    });
    await teamRepository.removeUserFromTeam(team.value.id, id, type);
    selectedUser.value = null;
    teamMembers.value = teamMembers.value.filter((user) => user.id !== id);
    addToast({
      type: "success",
      message: $t("The user has been removed from the team."),
    });
    emit("user-removed", id);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  }
}

async function handleRemoveUsers() {
  try {
    await confirm({
      title: $t("remove users from team"),
      message: $t("Are you sure you want to remove this users from this team?"),
      confirmOptions: {
        label: $t("remove"),
        variant: "danger",
      },
    });
    await api.delete(
      `teams/${team.value.id}/members`,
      {},
      {
        ids: selectedUsers.value.map((user) => user.id),
      },
    );
    selectedUser.value = null;
    selectedUsers.value = [];
    fetchTeam();
    addToast({
      type: "success",
      message: $t("The users has been removed from the team."),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  }
}

watchEffect(() => {
  if (selectedUser.value) {
    fetchUserProfile(selectedUser.value.id);
  }
});

const calculateHeight = () => {
  if (membersList.value && gridContainer.value) {
    const gridContainerHeight = gridContainer.value.clientHeight;
    const membersListHeight = membersList.value.$el.scrollHeight;
    membersList.value.$el.style.height =
      membersListHeight > gridContainerHeight ? `100%` : `${membersListHeight}px`;
    if (membersListHeight > gridContainerHeight) {
      membersList.value.$el.classList.add("pb-16");
    } else {
      membersList.value.$el.classList.remove("pb-16");
    }
  }
};

watch(
  () => teamMembers.value,
  async () => {
    await nextTick();
    calculateHeight();
  },
  { deep: true },
);

watch(
  () => team.value,
  async () => {
    selectedUser.value = null;
    teamMembers.value = team.value.users;
    /**
     * This is a workaround to make the members list
     * scrollable without using a fixed height
     */
    if (membersList.value) {
      membersList.value.$el.style.height = "fit-content";
    }
    await nextTick();
    calculateHeight();
  },
  { deep: true },
);
</script>
