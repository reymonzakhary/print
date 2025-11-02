<template>
  <component
    :is="props.to ? NuxtLink : 'button'"
    :to="props.to"
    class="flex items-center px-2 py-1 text-sm transition-colors duration-150 border rounded-full"
    :class="{
      'w-full': !noFullWidth,
      'cursor-not-allowed !border-gray-500 !text-gray-500 hover:!bg-transparent': props.disabled,
      'text-theme-500 border-theme-500 hover:border-theme-200 hover:bg-theme-200 hover:text-theme-700 dark:border-theme-300 dark:text-theme-300 dark:hover:text-theme-800':
        props.variant === 'primary',
      'text-green-500 border-green-500 hover:border-green-200 hover:bg-green-200 hover:text-green-700':
        props.variant === 'success',
      'text-red-500 border-red-500 hover:border-red-200 hover:bg-red-200 hover:text-red-700':
        props.variant === 'danger',
      'text-gray-500 border-gray-500 hover:border-gray-200 hover:bg-gray-200 hover:text-gray-700':
        props.variant === 'neutral',
      'text-white bg-theme-500 border-0 hover:bg-theme-600':
        props.variant === 'filled' && !props.disabled,
    }"
    :disabled="props.disabled"
  >
    <font-awesome-icon v-if="props.icon.length" :icon="props.icon" class="ml-1" fixed-width />
    <span class="flex-1 text-center" :class="{ '-ml-3': props.icon.length && !noFullWidth }">
      <slot />
    </span>
  </component>
</template>

<script setup>
import { NuxtLink } from "#components";

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: "primary",
  },
  icon: {
    type: Array,
    default: () => [],
  },
  noFullWidth: {
    type: Boolean,
    default: false,
  },
  to: {
    type: String,
    default: "",
  },
});
</script>
