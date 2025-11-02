<template>
  <div>
    <div v-if="loading">
      <SkeletonLine class="h-20" />
    </div>
    <div v-else class="flex items-center justify-between px-4 py-2 mb-2 bg-gray-50">
      <span class="w-1/3">{{ activeTeam.name }}</span>
      <span class="w-1/3 text-center text-theme-500">
        <font-awesome-icon :icon="['fal', 'arrow-left']" />
        <font-awesome-icon :icon="['fal', 'link']" />
        <font-awesome-icon :icon="['fal', 'arrow-right']" />
      </span>
      <span class="flex w-1/3">
        <v-select
          v-model="selectedMS"
          :options="chooseableMediaSources"
          :disabled="chooseableMediaSources.length === 0"
          label="name"
          class="flex-1 p-0 rounded-r-none input"
        />
        <button
          class="px-4 pb-1 rounded-r bg-theme-500 text-themecontrast-500"
          :class="{
            'cursor-not-allowed !bg-gray-300': chooseableMediaSources.length === 0,
            'hover:bg-theme-600': chooseableMediaSources.length > 0,
          }"
          :disabled="chooseableMediaSources.length === 0"
          @click="setMediasource(selectedMS.id)"
        >
          {{ $t("add") }}
        </button>
      </span>
    </div>
    <div
      v-for="(ms, i) in teamsMS"
      :key="`team_ms_${ms.id}_${i}`"
      class="flex items-center justify-between px-4 my-4 italic text-gray-500"
    >
      <span class="w-1/3">{{ activeTeam.name }}</span>
      <span class="w-1/3 text-center text-theme-500">
        <font-awesome-icon :icon="['fal', 'arrow-left']" />
        <font-awesome-icon :icon="['fal', 'link']" />
        <font-awesome-icon :icon="['fal', 'arrow-right']" />
      </span>
      <span class="flex items-center justify-between w-1/3">
        {{ ms.name }}
        <button class="px-2 text-red-500 rounded-full hover:bg-red-100" @click="removeMS(ms.id)">
          <font-awesome-icon :icon="['fal', 'trash-can']" />
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
const api = useAPI();
const route = useRoute();
const { t: $t } = useI18n();
const { addToast } = useToastStore();
const teamRepository = useTeamRepository();
const { handleError } = useMessageHandler();

const loading = ref(true);
const activeTeam = ref(null);
const mediaSources = ref([]);
const teamsMS = ref([]);
const selectedMS = ref(null);

const chooseableMediaSources = computed(() => {
  return mediaSources.value.filter((ms) => {
    return !teamsMS.value.find((teamMS) => teamMS.id === ms.id);
  });
});

onMounted(async () => {
  loading.value = true;
  await fetchTeam();
  await Promise.all([fetchMediaSources(), fetchTeamMediaSources()]);
  loading.value = false;
});

async function fetchTeam() {
  try {
    const team = await teamRepository.getTeamById(route.params.activeTeam);
    activeTeam.value = team;
  } catch (error) {
    handleError(error);
  }
}

async function setMediasource(mediaSourceId) {
  try {
    if (!activeTeam.value?.id) return;
    await teamRepository.addTeamMediaSource(activeTeam.value.id, mediaSourceId);
    fetchTeamMediaSources();
    selectedMS.value = null;
    addToast({
      type: "success",
      message: $t("Mediasource succesfully added to team"),
    });
  } catch (error) {
    handleError(error);
  }
}

async function fetchMediaSources() {
  try {
    if (!activeTeam.value?.id) return;
    const { data } = await api.get("media-sources");
    mediaSources.value = data;
  } catch (error) {
    handleError(error);
  }
}

async function fetchTeamMediaSources() {
  try {
    if (!activeTeam.value?.id) return;
    const data = await teamRepository.getTeamMediaSources(activeTeam.value.id);
    teamsMS.value = data;
  } catch (error) {
    handleError(error);
  }
}

async function removeMS(mediaSourceId) {
  try {
    await teamRepository.removeTeamMediaSource(activeTeam.value.id, mediaSourceId);
    fetchTeamMediaSources();
    addToast({
      type: "success",
      message: $t("Mediasource succesfully removed from team"),
    });
  } catch (error) {
    handleError(error);
  }
}
</script>
