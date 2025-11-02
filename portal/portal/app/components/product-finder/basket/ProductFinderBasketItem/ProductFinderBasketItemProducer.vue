<template>
  <div class="flex items-center gap-1">
    <font-awesome-icon :icon="['fas', 'building']" class="text-xs" />
    <span>{{ value || "N/A" }}</span>
  </div>
</template>

<script setup>
defineProps({
  value: {
    type: String,
    default: null,
  },
});
</script>
