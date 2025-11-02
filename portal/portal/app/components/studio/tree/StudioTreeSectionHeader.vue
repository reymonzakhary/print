<template>
  <div
    class="flex items-center justify-between border-b dark:border-gray-900"
    :class="{
      'cursor-pointer px-2 hover:bg-gray-200 dark:hover:bg-gray-900': !noToggle,
      'px-1 py-3': variant === 'normal',
      'px-2 py-2': variant === 'sub',
    }"
    @click="!noToggle && emit('toggle')"
  >
    <div class="flex items-center gap-2">
      <font-awesome-icon :icon="['fal', icon]" class="text-gray-400" fixed-width />
      <h3 class="text-sm font-semibold uppercase tracking-wider">
        {{ title }}
      </h3>
    </div>
    <font-awesome-icon
      v-if="!noToggle"
      :icon="['fal', expanded ? 'chevron-up' : 'chevron-down']"
      class="text-xs text-gray-500 transition-transform"
    />
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: "normal",
  },
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  expanded: {
    type: Boolean,
    default: true,
  },
  noToggle: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle"]);
</script>
