<template>
  <div
    class="flex h-20 flex-col items-center justify-center rounded-lg text-xs font-medium"
    :style="`background-color: ${color}; color: ${textColor};`"
  >
    <span>{{ label }}</span>
    <span class="mt-1 opacity-70">{{ color.toUpperCase() }}</span>
  </div>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    required: true,
  },
  textColor: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
});
</script>
