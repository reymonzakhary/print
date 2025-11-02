<template>
  <h2 :class="cn('text-lg font-medium text-gray-700 dark:text-gray-300', $attrs.class)">
    {{ title }}
  </h2>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
});

const { cn } = useUtilities();
</script>
