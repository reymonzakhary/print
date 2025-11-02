<template>
  <div>
    <span class="mb-1.5 ml-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
      {{ $t("Highlight:") }}
    </span>
    <div
      class="relative flex rounded-lg border border-gray-200 bg-white dark:border-gray-500 dark:bg-gray-700"
    >
      <button
        v-for="(option, index) in options"
        :key="option.value"
        :class="
          cn(
            'relative z-10 flex items-center truncate px-3 py-1 text-sm transition-all',
            modelValue === option.value
              ? 'bg-theme-50 font-medium text-theme-700 dark:bg-theme-900 dark:text-theme-500'
              : 'text-gray-600 hover:bg-gray-50 hover:text-theme-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-theme-500',
            index === 0 && 'rounded-l-md',
            index === options.length - 1 && 'rounded-r-md',
          )
        "
        @click="modelValue = option.value"
      >
        <font-awesome-icon :icon="option.icon" class="mr-1.5" />
        {{ option.label }}
      </button>
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
const modelValue = defineModel({ type: String, default: "deliveryTime" });

const { t: $t } = useI18n();
const { cn } = useUtilities();
</script>
