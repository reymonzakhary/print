<template>
  <p class="mb-2 text-xs font-bold tracking-wide uppercase">
    {{ $t("Create a new team") }}
  </p>
  <div class="flex items-center text-sm">
    <font-awesome-icon :icon="['fal', 'user-group-crown']" />
    <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
    <UIInputText
      v-model="newTeamName"
      name="teamName"
      :placeholder="$t('Team Name')"
      class="flex-1 border-r-0 rounded-r-none"
      input-class="rounded-r-none"
    />
    <button
      class="px-2 py-1 transition border border-l-0 border-gray-200 rounded-r text-themecontrast-900 bg-theme-400 hover:bg-theme-500 max-w-32 line-clamp-1"
      :class="{ 'cursor-not-allowed !bg-theme-100 hover:!bg-theme-100': newTeamName.length === 0 }"
      :disabled="newTeamName.length === 0"
      @click="handleAddTeam"
    >
      <font-awesome-icon :icon="['fal', 'plus']" />
    </button>
  </div>
</template>

<script setup>
const emit = defineEmits(["team-created"]);

const newTeamName = ref("");

async function handleAddTeam() {
  emit("team-created", newTeamName.value);
}
</script>
