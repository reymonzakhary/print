<template>
  <button class="inline-flex items-center justify-center px-2 py-1 text-xs rounded-full" :class="{
    'h-8 aspect-square': !$slots.default || $slots.default.length === 0 || $slots.default === '',
    'bg-white text-theme-500 enabled:hover:bg-theme-100': variant === 'default',
    'text-white bg-green-500 enabled:hover:bg-green-600': variant === 'success',
    'text-white bg-gray-500 enabled:hover:bg-gray-600': variant === 'neutral',
    'text-white bg-red-500 enabled:hover:bg-red-600': variant === 'danger',
    'opacity-50 cursor-not-allowed': disabled,
  }" @click="$emit('click')" :disabled="disabled">
    <font-awesome-icon v-if="icon" :icon="icon" :class="{ 'mr-1': $slots.default }" />
    <slot />
  </button>
</template>

<script>
/**
 * @deprecated since 14 nov 2023 - will eventually be removed. Use UIButton instead.
 */
export default {
  name: "UICardHeaderButton",
  props: {
    icon: {
      type: Array,
      required: false,
    },
    variant: {
      type: String,
      default: 'default',
      validator: (prop) => [
        'default',
        'neutral',
        'success',
        'danger',
      ].includes(prop)
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['click'],
  created() {
    if (process.env.NODE_ENV === 'development') {
      console.warn('UICardHeaderButton is deprecated and will be removed in future releases. Please use UIButton instead.');
    }
  }
}
</script>