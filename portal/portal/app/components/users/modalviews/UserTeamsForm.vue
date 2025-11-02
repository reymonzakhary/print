<template>
  <div>
    <UIInputText
      v-model="searchQuery"
      name="filter"
      placeholder="filter"
      class="mb-2 w-full"
      :icon="['fal', 'filter']"
    />

    <UILoader v-if="isLoading" />
    <UISwitchList v-else>
      <UISwitchListItem
        v-for="team in filteredTeams"
        :key="`team_${team.id}`"
        :name="team.name"
        :label="team.name"
        :model-value="!!selectedTeams.find((selected) => selected.id === team.id)"
        @update:model-value="updateTeams(team.id, 'toggleTeam')"
      >
        <UISwitchList
          v-if="selectedTeams.find((selected) => selected.id === team.id)"
          reverse-colors
        >
          <UISwitchListItem
            class="font-normal"
            :name="`${team.name}_admin`"
            :label="$t('team admin')"
            :model-value="!!selectedTeams.find((selected) => selected.id === team.id).admin"
            @update:model-value="updateTeams(team.id, 'toggleAdmin')"
          />

          <UISwitchListItem
            class="font-normal"
            :name="`${team.name}_authorizer`"
            :label="$t('team authorizer')"
            :model-value="!!selectedTeams.find((selected) => selected.id === team.id).authorizer"
            @update:model-value="updateTeams(team.id, 'toggleAuthorizer')"
          />
        </UISwitchList>
      </UISwitchListItem>
    </UISwitchList>
  </div>
</template>

<script setup>
const props = defineProps({
  user: {
    type: [Object, null],
    required: true,
  },
});

const emit = defineEmits(["teams-updated"]);

const { handleError } = useMessageHandler();

const isLoading = ref(true);
const selectedTeams = ref([]);

const { data: teams } = await useLazyAPI("/teams", {
  query: { per_page: 99999 },
  transform: ({ data }) => data,
  default: () => [],
});

// Use the fuzzy search composable
const { searchQuery, filteredResults: filteredTeams } = useFuzzySearch(teams, {
  keys: ["name"],
});

const populateSelectedTeams = () => {
  if (props.user.teams && props.user.teams.length > 0) selectedTeams.value = [];
  selectedTeams.value = props.user.teams.map((team) => ({
    id: team.id,
    admin: team.admin,
    authorizer: team.authorizer,
  }));
  isLoading.value = false;
};

watch(
  () => teams.value,
  () => {
    if (teams.value.length > 0) {
      populateSelectedTeams();
    }
  },
  { immediate: true },
);

const updateTeams = (teamId, action) => {
  const teamIndex = selectedTeams.value.findIndex((selected) => selected.id === teamId);

  switch (action) {
    case "toggleTeam":
      if (teamIndex === -1) {
        selectedTeams.value.push({
          id: teamId,
          admin: false,
          authorizer: false,
        });
      } else {
        selectedTeams.value.splice(teamIndex, 1);
      }
      break;
    case "toggleAdmin":
      selectedTeams.value[teamIndex].admin = !selectedTeams.value[teamIndex].admin;
      break;
    case "toggleAuthorizer":
      selectedTeams.value[teamIndex].authorizer = !selectedTeams.value[teamIndex].authorizer;
      break;
    default:
      handleError("Invalid action");
  }

  emit("teams-updated", selectedTeams.value);
};

const updateUser = async () => {
  try {
    await api.put(`${endpoint}/${props.user.id}`, {
      teams: selectedTeams.value,
      type: "individual",
    });
    addToast({
      message: $t(`successfully updated teams`),
      type: "success",
    });
    emit("teams-updated", selectedTeams.value);
    closeModal();
  } catch (err) {
    handleError(err);
  }
};
</script>
