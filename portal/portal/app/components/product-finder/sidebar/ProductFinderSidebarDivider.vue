<template>
  <div>
    <h3
      v-if="name !== '_'"
      class="mb-4 border-b border-gray-200 pb-2 text-lg font-semibold text-gray-800"
    >
      {{ name }}
    </h3>
    <div class="space-y-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    required: true,
  },
});
</script>
