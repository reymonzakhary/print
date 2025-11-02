<template>
  <h3
    :class="cn('mb-3 text-sm font-medium uppercase text-gray-600 dark:text-gray-400', props.class)"
  >
    {{ title }}
  </h3>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: "",
  },
  class: {
    type: String,
    default: "",
  },
});
const { cn } = useUtilities();
</script>
