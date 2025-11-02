<template>
  <ul>
    <li
      v-for="i in skeletonLineAmount"
      :key="`skeletonListLine_${i}`"
      class="loading my-2 w-full rounded dark:bg-gray-900"
      :class="`h-${props.skeletonLineHeight * 2} bg-gray-${props.skeletonTone}`"
    />
  </ul>
</template>

<script setup>
defineOptions({
  name: "TheListSkeleton",
});

const props = defineProps({
  skeletonLineHeight: {
    type: Number,
    default: 6,
  },
  skeletonLineAmount: {
    type: Number,
    default: 6,
  },
  skeletonTone: {
    type: Number,
    default: 200,
  },
});
</script>
