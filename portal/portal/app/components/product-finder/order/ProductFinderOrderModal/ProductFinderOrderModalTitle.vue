<template>
  <h1 class="text-2xl font-semibold dark:text-white">
    {{ title }}
  </h1>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
});
</script>
