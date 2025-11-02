<template>
  <div class="col-span-2 row-span-full flex flex-col items-center justify-center py-12">
    <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-900/30">
      <font-awesome-icon
        :icon="['fad', 'face-sad-tear']"
        class="h-12 w-12 text-gray-500 dark:text-gray-400"
      />
    </div>
    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
      {{ $t("Whoops, no producers found") }}...
    </h3>
    <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
      {{ $t("This category does not have any producers yet. Please try again later.") }}
    </p>
    <button
      class="mt-6 rounded-md bg-theme-400 px-4 py-2 text-white hover:bg-theme-500 dark:bg-theme-300 dark:hover:bg-theme-400"
      @click="navigateTo(`/marketplace/product-finder`)"
    >
      {{ $t("Try a different category") }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  error: {
    type: Number,
    required: true,
  },
});
</script>
