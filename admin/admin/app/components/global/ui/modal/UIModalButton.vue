<template>
  <button
    :disabled="disabled"
    class="px-5 py-1 mr-2 text-sm text-white transition-colors duration-300 ease-in-out rounded-full"
    :class="{
      'bg-red-500 hover:bg-red-700': variant === 'danger' && !disabled,
      'bg-red-300 !hover:bg-red-300': variant === 'danger' && disabled,
      'bg-green-500 hover:bg-green-700': variant === 'success' && !disabled,
      'bg-green-300 hover:bg-green-300': variant === 'success' && disabled,
      'bg-gray-500 hover:bg-gray-700': variant === 'default' && !disabled,
      'bg-gray-300 hover:bg-gray-300': variant === 'default' && disabled,
      'bg-blue-500 hover:bg-blue-700': variant === 'blue' && !disabled,
      'bg-blue-300 hover:bg-blue-300': variant === 'blue' && disabled,
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
        return ["default", "danger", "success"].includes(variant);
      },
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
