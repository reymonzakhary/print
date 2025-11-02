<template>
  <component
    :is="props.to ? NuxtLink : 'button'"
    :to="props.to"
    class="inline-flex items-center justify-center px-2 py-1 text-xs transition rounded-full"
    :class="{
      'h-8 !p-0 aspect-square': !$slots.default,
      'bg-white dark:bg-black text-theme-500 dark:text-theme-100 hover:bg-theme-100 dark:hover:bg-theme-700':
        variant === 'default',
      'bg-transparent text-theme-500 dark:text-theme-100 hover:!bg-theme-100 dark:hover:!bg-theme-900':
        variant === 'link',
      'bg-white text-gray-500 hover:bg-gray-100': variant === 'inverted-neutral',
      'bg-white text-green-500 hover:bg-green-100': variant === 'inverted-success',
      'bg-white text-amber-500 hover:bg-amber-100': variant === 'inverted-warning',
      'bg-white text-red-500 hover:bg-red-100': variant === 'inverted-danger',
      'text-white bg-green-400 hover:bg-green-500': variant === 'success',
      'text-white bg-gray-500 hover:bg-gray-600': variant === 'neutral',
      'text-black bg-gray-200 dark:bg-gray-700 dark:text-theme-100 dark:hover:bg-gray-600 hover:bg-gray-300':
        variant === 'neutral-light',
        'bg-amber-500 text-white hover:bg-amber-600': variant === 'warning',
      'text-white bg-red-500 hover:bg-red-600': variant === 'danger',
      'text-themecontrast-400 bg-theme-500 hover:bg-theme-600 disabled:hover:bg-theme-500 hover:text-themecontrast-600':
        variant === 'theme',
      'text-theme-900 bg-theme-100 hover:bg-theme-300 hover:text-white': variant === 'theme-light',
      'outline-transparent outline outline-1': variant === 'outline',
      '!opacity-50 !cursor-not-allowed': disabled,
    }"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    <font-awesome-icon
      v-if="icon.length > 0 && iconPlacement === 'left'"
      :icon="icon"
      :class="[{ 'mr-1': $slots.default }, iconClass]"
    />
    <slot />
    <font-awesome-icon
      v-if="icon.length > 0 && iconPlacement === 'right'"
      :icon="icon"
      :class="[{ 'ml-1': $slots.default }, iconClass]"
    />
  </component>
</template>

<script setup lang="ts">
import { NuxtLink } from "#components";

type Variant =
  | "default"
  | "link"
  | "neutral"
  | "neutral-light"
  | "inverted-neutral"
  | "success"
  | "inverted-success"
  | "warning"
  | "inverted-warning"
  | "danger"
  | "inverted-danger"
  | "theme"
  | "theme-light"
  | "outline";

interface ButtonProps {
  icon?: string | string[];
  variant?: Variant;
  disabled?: boolean;
  iconPlacement?: "left" | "right";
  iconClass?: string;
  to?: string;
}

const props = withDefaults(defineProps<ButtonProps>(), {
  icon: () => [],
  variant: "default",
  disabled: false,
  iconPlacement: "left",
  iconClass: "",
  to: "",
});

const { icon, variant, disabled, iconPlacement } = toRefs(props);

defineEmits<{
  (e: "click", event: MouseEvent): void;
}>();
</script>
