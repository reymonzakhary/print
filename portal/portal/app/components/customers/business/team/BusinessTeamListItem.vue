<template>
  <UICard
    class="relative mt-2 text-sm transition-colors duration-75 rounded cursor-pointer hover:bg-theme-50 dark:hover:bg-gray-700"
  >
    <div class="p-3 grid grid-cols-3 items-center">
      <span>{{ team.name }}</span>
      <span>{{ team.members }}</span>
      <UIButton
        :icon="['fal', 'trash']"
        variant="danger"
        class="ml-auto"
        @click.prevent.stop="() => {}"
      />
    </div>
  </UICard>
</template>

<script setup>
const { team } = defineProps({
  team: {
    type: Object,
    required: true,
    validator: (value) => {
      const requiredList = ["id", "name", "members"];
      return requiredList.every((key) => key in value);
    },
  },
});
</script>
