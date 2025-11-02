<template>
  <div
    v-if="options && options.length > 0"
    class="mt-6 space-y-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700"
  >
    <h3 class="font-medium text-gray-900 dark:text-white">
      {{ $t("Your selection options") }}
    </h3>
    <div class="space-y-3">
      <div v-for="(option, idx) in options" :key="idx" class="flex items-center justify-between">
        <label
          :for="`option-${idx}`"
          class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
        >
          <input
            :id="`option-${idx}`"
            v-model="option.selected"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-theme-500 focus:ring-theme-500 dark:border-gray-600"
          />
          {{ option.name }}
        </label>
        <span v-if="option.price" class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ option.price }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  options: {
    type: Array,
    default: () => [],
  },
});

const { t: $t } = useI18n();
</script>
