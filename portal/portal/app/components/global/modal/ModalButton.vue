<template>
  <button
    :disabled="disabled"
    class="mr-2 rounded-full px-5 py-1 text-sm text-white transition-colors duration-300 ease-in-out"
    :class="{
      'bg-red-500 hover:bg-red-700': variant === 'danger' && !disabled,
      '!hover:bg-red-300 bg-red-300': variant === 'danger' && disabled,
      'bg-green-500 hover:bg-green-700': variant === 'success' && !disabled,
      'bg-green-300 hover:bg-green-300': variant === 'success' && disabled,
      'bg-gray-500 hover:bg-gray-700': variant === 'default' && !disabled,
      'bg-gray-300 hover:bg-gray-300': variant === 'default' && disabled,
      'bg-theme-500 hover:bg-theme-700': variant === 'theme' && !disabled,
      'bg-theme-300 hover:bg-theme-300': variant === 'theme' && disabled,
      'cursor-not-allowed': disabled,
    }"
  >
    <slot />
  </button>
</template>

<script>
export default {
  name: "ModalButton",
  props: {
    variant: {
      type: String,
      default: "default",
      validator: function (variant) {
        return ["default", "danger", "success", "theme"].includes(variant);
      },
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
