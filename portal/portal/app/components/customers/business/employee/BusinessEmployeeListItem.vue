<template>
  <UICard
    class="relative mt-2 text-sm transition-colors duration-75 rounded cursor-pointer hover:bg-theme-50 dark:hover:bg-gray-700"
  >
    <div class="p-3 grid grid-cols-4 items-center">
      <span>{{ employee.name }}</span>
      <span>{{ employee.email }}</span>
      <span>{{ employee.phone }}</span>
      <span class="flex overflow-x-auto">
        <span
          v-for="team in employee.teams"
          :key="team"
          class="capitalize p-1 bg-theme-100 rounded-full px-4 mr-2"
          >{{ team }}</span
        >
      </span>
    </div>
  </UICard>
</template>

<script setup>
const { employee } = defineProps({
  employee: {
    type: Object,
    required: true,
    validator: (value) => {
      const requiredList = ["id", "name", "email", "phone", "teams"];
      return requiredList.every((key) => key in value);
    },
  },
});
</script>
