<template>
  <li
    class="grid items-center text-xs"
    :class="{ 'grid-cols-1': compact, 'grid-cols-[33%_67%]': !compact }"
  >
    <span class="whitespace-nowrap text-gray-700 dark:text-gray-300">{{ label }}:</span>
    <span
      class="font-bold text-gray-900 dark:text-white"
      :class="{ 'whitespace-nowrap text-right': !compact }"
    >
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
  compact: {
    type: Boolean,
    default: false,
  },
});
</script>
