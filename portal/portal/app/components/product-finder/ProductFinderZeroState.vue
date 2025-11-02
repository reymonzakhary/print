<template>
  <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
    <div class="mb-6 rounded-full bg-gray-100 p-6 dark:bg-gray-800">
      <font-awesome-icon :icon="icon" class="size-12 text-gray-400 dark:text-gray-500" />
    </div>
    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">
      {{ title }}
    </h3>
    <p class="max-w-md text-gray-500 dark:text-gray-400">
      {{ description }}
    </p>
    <button
      v-if="showClearButton"
      class="mt-6 flex items-center gap-2 rounded-lg bg-theme-50 px-4 py-2 text-sm font-medium text-theme-700 transition-colors hover:bg-theme-100 dark:bg-theme-900 dark:text-theme-300 dark:hover:bg-theme-800"
      @click="$emit('clear')"
    >
      <font-awesome-icon :icon="['fal', 'times']" class="size-4" />
      <span>{{ clearButtonText }}</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: Array,
    default: () => ["fal", "search"],
  },
  title: {
    type: String,
    default: "No results found",
  },
  description: {
    type: String,
    default:
      "We couldn't find any results for your search query. Please try again with a different search term.",
  },
  showClearButton: {
    type: Boolean,
    default: false,
  },
  clearButtonText: {
    type: String,
    default: "Clear search query",
  },
});

defineEmits(["clear"]);
</script>
