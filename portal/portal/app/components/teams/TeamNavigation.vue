<template>
  <UICardHeader>
    <template #left>
      <UICardHeaderTitle :icon="['fas', 'user-group-crown']" :title="props.activeTeam" />
    </template>
    <template #right>
      <nav role="navigation" class="flex" aria-label="Team navigation">
        <NuxtLink
          :to="{ name: 'manage-teams-activeTeam-members', params: { activeTeam: activeTeamId } }"
        >
          <UICardHeaderTab
            :active="route.name === 'manage-teams-activeTeam-members'"
            :label="$t('Team Members')"
            role="tab"
            :aria-selected="route.name === 'manage-teams-activeTeam-members'"
          />
        </NuxtLink>
        <NuxtLink
          :to="{
            name: 'manage-teams-activeTeam-accessibility',
            params: { activeTeam: activeTeamId },
          }"
          role="tab"
          :aria-selected="route.name === 'manage-teams-activeTeam-accessibility'"
        >
          <UICardHeaderTab
            :active="route.name === 'manage-teams-activeTeam-accessibility'"
            :label="$t('Accessibility')"
          />
        </NuxtLink>
        <NuxtLink
          :to="{
            name: 'manage-teams-activeTeam-addresses',
            params: { activeTeam: activeTeamId },
          }"
          role="tab"
          :aria-selected="route.name === 'manage-teams-activeTeam-addresses'"
        >
          <UICardHeaderTab
            :active="route.name === 'manage-teams-activeTeam-addresses'"
            :label="$t('Addresses')"
          />
        </NuxtLink>
        <NuxtLink
          :to="{
            name: 'manage-teams-activeTeam-media-sources',
            params: { activeTeam: activeTeamId },
          }"
          role="tab"
          :aria-selected="route.name === 'manage-teams-activeTeam-media-sources'"
        >
          <UICardHeaderTab
            :active="route.name === 'manage-teams-activeTeam-media-sources'"
            :label="$t('Media Sources')"
          />
        </NuxtLink>
      </nav>
    </template>
  </UICardHeader>
</template>

<script setup>
const props = defineProps({
  activeTeam: {
    type: String,
    required: true,
  },
});

const route = useRoute();
const activeTeamId = computed(() => route.params.activeTeam);
</script>
