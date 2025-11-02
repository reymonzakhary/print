<template>
  <div
    :class="[
      { rounded: roundedFull, 'rounded-b': !roundedFull },
      `shadow-md ${shadowColor} bg-white dark:bg-gray-700`,
    ]"
  >
    <slot />
  </div>
</template>

<script>
export default {
  name: "UICard",
  props: {
    roundedFull: {
      type: Boolean,
      default: false,
      required: false,
    },
    shadowColor: {
      type: String,
      default: "shadow-gray-200 dark:shadow-gray-900",
      required: false,
    },
  },
};
</script>
