<template>
  <component
    :is="props.to ? NuxtLink : 'button'"
    :to="props.to"
    class="inline-flex items-center justify-center rounded-full px-2 py-1 text-xs transition-all"
    :class="{
      'aspect-square h-8 !p-0': !$slots.default,
      'bg-white text-theme-500 hover:bg-theme-100 dark:bg-gray-700 dark:text-theme-100 dark:hover:bg-theme-700':
        props.variant === 'default',
      'bg-transparent text-theme-500 hover:!bg-theme-100 dark:text-theme-100 dark:hover:!bg-theme-900':
        props.variant === 'link',
      'bg-white text-gray-500 hover:bg-gray-100': props.variant === 'inverted-neutral',
      'bg-white text-green-500 hover:bg-green-100': props.variant === 'inverted-success',
      'bg-white text-red-500 hover:bg-red-100': props.variant === 'inverted-danger',
      'bg-white text-amber-500 hover:bg-amber-100': props.variant === 'inverted-warning',
      'bg-green-500 text-white hover:bg-green-600': props.variant === 'success',
      'bg-gray-500 text-white hover:bg-gray-600': props.variant === 'neutral',
      'bg-gray-200 text-black hover:bg-gray-300 dark:bg-gray-700 dark:text-theme-100 dark:hover:bg-gray-600':
        props.variant === 'neutral-light',
      'bg-red-500 text-white hover:bg-red-600': props.variant === 'danger',
      'bg-amber-500 text-white hover:bg-amber-600': props.variant === 'warning',
      'bg-theme-400 text-themecontrast-400 hover:bg-theme-600 hover:text-themecontrast-600 disabled:hover:bg-theme-500':
        props.variant === 'theme',
      'bg-theme-100 text-theme-900 hover:bg-theme-300 hover:text-white':
        props.variant === 'theme-light',
      'outline outline-1 outline-transparent': props.variant === 'outline',
      '!cursor-not-allowed !opacity-50': disabled,
      'aspect-square h-8 !p-0': !$slots.default,
    }"
    :disabled="props.disabled"
    @click="emit('click', $event)"
  >
    <font-awesome-icon
      v-if="props.icon.length > 0 && props.iconPlacement === 'left'"
      :icon="props.icon"
      :class="[{ 'mr-1': $slots.default }, props.iconClass]"
    />
    <slot />
    <font-awesome-icon
      v-if="props.icon.length > 0 && props.iconPlacement === 'right'"
      :icon="props.icon"
      :class="[{ 'ml-1': $slots.default }, props.iconClass]"
    />
  </component>
</template>

<script setup>
import { NuxtLink } from "#components";

const props = defineProps({
  icon: {
    type: [String, Array],
    default: () => [],
  },
  variant: {
    type: String,
    default: "default",
    validator: (value) =>
      [
        "default",
        "link",
        "neutral",
        "neutral-light",
        "inverted-neutral",
        "success",
        "inverted-success",
        "warning",
        "danger",
        "inverted-warning",
        "inverted-danger",
        "theme",
        "theme-light",
        "outline",
      ].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  iconPlacement: {
    type: String,
    default: "left",
    validator: (value) => ["left", "right"].includes(value),
  },
  iconClass: {
    type: String,
    default: "",
  },
  to: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["click"]);
</script>
