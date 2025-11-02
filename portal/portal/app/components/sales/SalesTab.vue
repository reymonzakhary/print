<template>
  <button
    class="block px-6 py-2 capitalize transition-colors duration-200 rounded-t focus:outline-none"
    :class="{
      'text-themecontrast-400 font-semibold bg-theme-400 dark:bg-theme-400 dark:hover:bg-theme-400':
        props.active,
      'bg-theme-100 dark:bg-theme-600 hover:bg-theme-200 dark:hover:bg-theme-700': !props.active,
      'cursor-not-allowed hover:!bg-theme-100 dark:hover:!bg-theme-600 opacity-75': props.disabled,
    }"
    @click="handleClick"
  >
    <font-awesome-icon :icon="props.icon" />
    <span class="hidden ml-2 md:inline-block">{{ props.label }}</span>
  </button>
</template>

<script setup>
const props = defineProps({
  active: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["click"]);

function handleClick() {
  if (!props.disabled) {
    emit("click");
  }
}
</script>
