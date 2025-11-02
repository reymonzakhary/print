<template>
  <article>
    <UICard class="backdrop-blur">
      <div class="grid grid-cols-3 py-2 px-3 text-xs font-bold uppercase">
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("name") }}
        </div>
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("members") }}
        </div>
        <div
          class="w-full overflow-hidden whitespace-nowrap text-ellipsis text-right"
        >
          {{ $t("actions") }}
        </div>
      </div>
    </UICard>
    <Nuxt-Link
      v-for="team in teams"
      :key="team.id"
      :to="`/customers/business/${businessId}/teams/${team.id}`"
    >
      <BusinessTeamListItem :team="team" />
    </Nuxt-Link>
  </article>
</template>

<script setup>
const { businessId, teams } = defineProps({
  businessId: {
    type: [String, Number],
    required: true,
  },
  teams: {
    type: Array,
    required: true,
  },
});
</script>
