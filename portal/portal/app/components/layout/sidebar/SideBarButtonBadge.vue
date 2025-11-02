<template>
  <span class="rounded-full bg-theme-500 px-1 py-0 text-xs text-white">
    <font-awesome-icon :icon="icon" />
    {{ label }}
  </span>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    default: "beta",
  },
  icon: {
    type: Array,
    default: () => ["fal", "sparkles"],
  },
});
</script>
