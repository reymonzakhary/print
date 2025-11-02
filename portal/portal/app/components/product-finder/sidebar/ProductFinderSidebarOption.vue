<template>
  <button
    :disabled="disabled"
    :class="
      cn(
        'min-w-[100px] flex-grow rounded-md border border-gray-200 bg-white px-4 py-2.5 text-center text-sm text-gray-700 transition-all dark:border-gray-500 dark:bg-gray-800 dark:text-gray-400',
        !disabled && 'hover:bg-gray-100',
        isSelected &&
          'border-theme-300 bg-theme-50 text-theme-700 dark:border-theme-600 dark:bg-theme-700 dark:text-theme-300',
        isSelected && !disabled && 'hover:bg-theme-100',
        disabled && 'cursor-not-allowed opacity-50',
        $attrs.class,
      )
    "
  >
    <span v-if="name">{{ name }}</span>
    <slot v-else />
  </button>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    required: false,
    default: null,
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const { cn } = useUtilities();
</script>
