<template>
  <div class="flex -space-x-4">
    <template v-for="(producer, index) in producers.slice(0, 3)" :key="producer.name">
      <div
        class="relative grid size-8 place-items-center overflow-hidden rounded-full border-2 border-gray-200 bg-white shadow-sm dark:border-gray-600 dark:bg-gray-800"
        :style="{ zIndex: 5 - index }"
      >
        <NuxtImg
          v-if="producer.logo"
          :src="producer.logo"
          :alt="producer.name"
          class="size-full object-contain p-0.5"
        />
        <font-awesome-icon v-else :icon="['fas', 'building']" class="text-xs text-gray-400" />
      </div>
    </template>
    <div
      v-if="producers.length > 3"
      class="relative -ml-2 flex size-8 items-center justify-center rounded-full border-2 border-white bg-theme-50 text-xs font-medium text-theme-600 shadow-sm dark:bg-theme-500 dark:text-theme-100"
    >
      +{{ producers.length - 3 }}
    </div>
    <p class="p-2 pl-6">{{ producers.length }} {{ $t("Producers", producers.length) }}</p>
  </div>
</template>

<script setup>
defineProps({
  producers: {
    type: Array,
    required: true,
  },
});
</script>
