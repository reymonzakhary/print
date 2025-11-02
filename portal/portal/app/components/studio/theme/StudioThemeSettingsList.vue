<template>
  <div>
    <div
      class="overflow-hidden rounded-lg border bg-gray-100 shadow-sm dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="bg-gray-10 border-b bg-gray-100 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-medium uppercase tracking-wide text-gray-700 dark:text-gray-300">
          <font-awesome-icon :icon="['fal', icon]" class="mr-2 text-theme-400" />
          {{ title }}
        </h3>
      </div>
      <div class="divide-y dark:divide-gray-700">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
});
</script>
