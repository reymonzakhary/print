<template>
  <ul
    :class="
      cn(
        '[&>li:not(:first-child)]:border-t [&>li:not(:first-child)]:border-gray-200 dark:[&>li:not(:first-child)]:border-gray-800 [&>li:nth-child(odd)]:bg-gray-50 dark:[&>li:nth-child(odd)]:bg-gray-900',
      )
    "
  >
    <TransitionGroup name="list">
      <slot />
    </TransitionGroup>
  </ul>
</template>

<script setup>
defineProps({
  reverseColors: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const { cn } = useUtilities();
</script>
