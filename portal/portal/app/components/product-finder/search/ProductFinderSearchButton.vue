<template>
  <button
    :class="
      cn(
        '-ml-2 flex w-full items-center gap-1 rounded-lg bg-white p-2 text-sm text-gray-700 dark:bg-gray-700',
        disabled && 'cursor-not-allowed',
        !disabled && 'hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-600',
        $attrs.class,
      )
    "
    :disabled="disabled"
  >
    <ProductFinderSearchIcon v-if="variant" :variant="variant" class="mr-1" />
    <slot />
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: "search",
    validator(value) {
      return ["search", "smart"].includes(value);
    },
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const { cn } = useUtilities();
</script>
