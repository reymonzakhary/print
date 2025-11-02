<template>
  <button
    class="group relative aspect-square overflow-hidden rounded-lg transition-transform"
    :class="
      active
        ? 'scale-105 shadow-xl outline outline-2 outline-gray-900 hover:scale-100 hover:shadow-lg dark:outline-gray-100'
        : 'shadow-lg hover:scale-105 hover:shadow-xl'
    "
    :style="`background-color: ${color};`"
  >
    <div
      class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100"
    >
      <span
        class="rounded-md bg-black/40 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm"
      >
        {{ label }}
      </span>
    </div>
  </button>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
});
</script>
