<template>
  <div class="relative block bg-gray-200 rounded dark:bg-gray-900 loading" :class="`h-${height}`" />
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
