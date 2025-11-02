<template>
  <button
    class="mx-auto rounded-full px-4 py-2 transition-colors duration-75"
    :class="{
      'bg-theme-400 text-themecontrast-400 hover:bg-theme-500': variant === 'primary',
      'border border-theme-500 bg-transparent text-theme-500 hover:bg-theme-400 dark:hover:bg-theme-100':
        variant === 'secondary',
      'cursor-not-allowed border !border-gray-400 !bg-gray-200 !text-gray-400 dark:!border-gray-700 dark:!bg-gray-700 dark:!text-gray-600':
        disabled,
    }"
    :disabled="disabled"
    @click="emit('click')"
  >
    <font-awesome-icon v-if="icon.length" :icon="icon" class="mr-2" />
    <slot />
  </button>
</template>

<script setup>
defineProps({
  icon: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: "primary",
    validator: (value) => ["primary", "secondary"].includes(value),
  },
});

const emit = defineEmits(["click"]);
</script>
