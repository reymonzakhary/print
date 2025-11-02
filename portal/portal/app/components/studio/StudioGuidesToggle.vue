<template>
  <button
    class="absolute right-4 top-4 z-20 rounded-full border bg-gradient-to-tr from-white to-gray-100 px-3 py-2 transition-all duration-300 dark:from-gray-700 dark:to-gray-800"
    :class="
      selected
        ? 'shadow-[inset_0_2px_4px_rgba(0,0,0,0.7)]'
        : 'shadow-[inset_1px_-1px_3px_rgba(255,255,255,0.3)]'
    "
    @click="selected = !selected"
  >
    <font-awesome-icon :icon="['fal', 'ruler']" />
    <font-awesome-icon
      v-if="selected"
      :icon="['fas', 'check']"
      class="absolute bottom-1 right-2 text-xs"
    />
  </button>
</template>

<script setup>
const selected = defineModel({ type: Boolean, required: false, default: false });
</script>
