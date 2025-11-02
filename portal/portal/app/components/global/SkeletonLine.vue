<template>
  <div class="loading relative block rounded bg-gray-200 dark:bg-gray-900" :class="`h-${height}`" />
</template>

<script>
export default {
  name: "SkeletonLine",
  props: {
    height: {
      type: Number,
      default: 2,
      required: false,
    },
  },
};
</script>
