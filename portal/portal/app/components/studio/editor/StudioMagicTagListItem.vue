<!-- components/MagicTagItem.vue -->
<template>
  <button
    class="from-theme-500/10 hover:from-theme-500/20 focus:ring-theme-500/30 group flex w-full cursor-pointer items-center justify-between rounded-md border bg-gradient-to-br to-purple-500/10 px-3 py-2 shadow-inner transition-all duration-200 hover:to-purple-500/20 hover:shadow-sm focus:border-theme-400 focus:outline-none focus:ring-2 dark:border-gray-900 dark:hover:border-gray-600"
    :title="`Click to copy: ${tag.description}`"
    @click="$emit('click', tag)"
  >
    <div class="flex items-center gap-2">
      <!-- Tag icon to indicate it's a dynamic tag -->
      <div
        class="flex size-5 items-center justify-center rounded bg-gradient-to-br from-theme-500 to-purple-500 text-white"
      >
        <font-awesome-icon :icon="['fal', 'tag']" class="text-xs" />
      </div>

      <div class="flex flex-col items-start gap-0.5">
        <span class="text-sm font-medium">{{ tag.display }}</span>
        <code class="font-mono text-xs text-gray-400">{{ tag.name }}</code>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <!-- Copy indicator -->
      <span
        class="text-xs text-theme-300 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
      >
        Click to copy
      </span>
      <font-awesome-icon
        :icon="['fal', 'copy']"
        class="text-gray-500 opacity-60 transition-all duration-200 group-hover:scale-110 group-hover:text-theme-400 group-hover:opacity-100"
      />
    </div>
  </button>
</template>

<script setup>
defineProps({
  tag: {
    type: Object,
    required: true,
  },
  showCopy: {
    type: Boolean,
    default: true,
  },
});

defineEmits(["click"]);
</script>
