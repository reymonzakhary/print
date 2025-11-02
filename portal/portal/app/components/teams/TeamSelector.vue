<template>
  <div>
    <UICard class="mb-2 bg-white p-2 dark:bg-gray-900" rounded-full>
      <TeamInputForm @team-created="handleTeamCreated" />
    </UICard>
    <div class="flex-1 overflow-y-auto">
      <template v-if="props.loading">
        <SkeletonLine v-for="i in 6" :key="i" class="mb-2 p-4" />
      </template>
      <template v-else>
        <ul>
          <li v-for="team in props.teams" :key="team.id">
            <NuxtLink
              :to="{ name: teamPageLink, params: { activeTeam: team.id } }"
              class="group flex w-full items-center justify-between rounded border-b border-b-gray-200 p-2 text-left capitalize hover:bg-gray-200 hover:py-1"
              active-class="bg-theme-50 text-theme-800 hover:!bg-theme-50"
            >
              <span>{{ team.name }}</span>
              <UIButton
                v-if="permissions.includes('teams-delete')"
                icon="trash"
                variant="inverted-danger"
                class="hidden border border-red-200 !bg-transparent hover:!bg-red-200 group-hover:block"
                @click.prevent.stop="handleDeleteTeam(team.id)"
              />
            </NuxtLink>
          </li>
        </ul>
      </template>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  teams: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["team-created", "team-deleted"]);

const route = useRoute();
const { t: $t } = useI18n();
const { confirm } = useConfirmation();
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const teamRepository = useTeamRepository();
const { permissions } = storeToRefs(useAuthStore());

const currentPageSuffix = computed(() => {
  const routeName = route.name?.toString() || "";
  const match = routeName.match(/manage-teams-activeTeam-(.+)/);
  return match ? match[1] : "members"; // default to members if no match
});

const teamPageLink = computed(() => `manage-teams-activeTeam-${currentPageSuffix.value}`);

async function handleTeamCreated(name) {
  try {
    const data = await teamRepository.createTeam(name);
    addToast({
      type: "success",
      message: "The team has been successfully created.",
    });
    emit("team-created", data);
  } catch (err) {
    handleError(err);
  }
}

async function handleDeleteTeam(teamId) {
  try {
    await confirm({
      title: $t("remove team"),
      message: $t("Are you sure you want to remove this team?"),
      confirmOptions: {
        label: $t("remove"),
        variant: "danger",
      },
    });
    await teamRepository.deleteTeam(teamId);
    addToast({
      type: "success",
      message: "The team has been successfully deleted.",
    });
    emit("team-deleted", teamId);
  } catch (err) {
    if (err.cancelled) return;
    handleError(err);
  }
}
</script>
