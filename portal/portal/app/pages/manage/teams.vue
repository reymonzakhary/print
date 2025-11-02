<template>
  <div class="grid h-full grid-cols-12 gap-4 p-4">
    <aside class="col-span-12 overflow-hidden md:col-span-3 2xl:col-span-2">
      <TeamSelector
        :loading="loading"
        :teams="teams.map((team) => ({ id: team.id, name: team.name }))"
        @team-created="fetchTeams"
        @team-deleted="fetchTeams"
      />
    </aside>
    <main v-if="activeTeam" class="col-span-12 gap-4 overflow-hidden md:col-span-9 2xl:col-span-10">
      <TeamNavigation :active-team="activeTeam.name" class="mb-2" />
      <NuxtPage />
    </main>
  </div>
</template>

<script setup>
// imports
const route = useRoute();
const { handleError } = useMessageHandler();
const teamRepository = useTeamRepository();

const loading = ref(false);
const teams = ref([]);

const activeTeamId = computed(() => route.params.activeTeam);
const activeTeam = computed(() => teams.value.find((team) => team.id == activeTeamId.value));

onMounted(async () => {
  await fetchTeams();
});

// methods
async function fetchTeams() {
  try {
    loading.value = true;
    const data = await teamRepository.getAllTeams();
    const dataWithLowerCasedNames = data.map((team) => ({
      ...team,
      name: team.name.toLowerCase(),
    }));
    teams.value = dataWithLowerCasedNames;
    loading.value = false;
  } catch (error) {
    handleError(error);
  }
}
</script>
