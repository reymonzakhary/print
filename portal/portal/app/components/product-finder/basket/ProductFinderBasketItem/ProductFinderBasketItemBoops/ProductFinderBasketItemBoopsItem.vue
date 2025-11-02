<template>
  <li class="grid grid-cols-1 items-center text-sm">
    <span class="text-gray-700 dark:text-gray-300">{{ label }}:</span>
    <span class="font-medium text-gray-900 dark:text-white">
      {{ value }}
    </span>
  </li>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
});
</script>
