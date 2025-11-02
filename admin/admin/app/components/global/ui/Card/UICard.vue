<template>
  <div
    :class="[
      { rounded: roundedFull, 'rounded-b': !roundedFull },
      `shadow-md shadow-${shadowColor}-200 dark:shadow-gray-900-md bg-white dark:bg-gray-700 dark:shadow-gray-900`,
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
      default: "gray",
      required: false,
    },
  },
};
</script>
