<template>
  <label :for="props.for" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
    {{ props.label }}
  </label>
</template>

<script setup>
const props = defineProps({
  for: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
});
</script>
