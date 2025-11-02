<template>
  <div class="relative mb-4">
    <div class="flex items-center">
      <button
        v-if="showLeftScrollButton"
        class="flex h-full flex-shrink-0 items-center justify-center px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
        aria-label="Scroll left"
        @click="scrollMenu('left')"
      >
        <font-awesome-icon :icon="['fas', 'chevron-left']" />
      </button>
      <div ref="menuRef" class="scrollbar-hide no-wrap flex flex-1 gap-2 overflow-x-auto">
        <button
          v-for="(step, index) in steps"
          :key="step.id"
          :class="
            cn(
              'flex flex-shrink-0 items-center gap-1.5 rounded-md border border-gray-100 bg-white px-3 py-1.5 text-sm text-gray-600 transition-colors hover:border-gray-200 hover:bg-gray-100 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700',
              currentStepIndex === index &&
              'bg-theme-50 font-medium text-theme-700 dark:bg-theme-900 dark:text-theme-500 border-theme-100',
              selectedOptions[step.linked] && 'text-green-700 bg-green-50 border-green-100 dark:bg-green-950 dark:border-green-900',
              !selectedOptions['quantity'] &&
              step.linked !== 'quantity' &&
              'cursor-not-allowed opacity-50',
            )
          "
          :disabled="!selectedOptions['quantity'] && step.linked !== 'quantity'"
          @click="emit('jump-to-step', index)"
        >
          <span v-if="selectedOptions[step.linked]" class="text-green-700 dark:text-green-400">
            <font-awesome-icon :icon="['fas', 'check']" class="size-3" />
          </span>
          <span v-else class="text-gray-500 dark:text-gray-400" :class="{'text-theme-700': currentStepIndex === index}">
            <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="size-3" />
          </span>
          {{ $display_name(step.display_name) }}
        </button>
      </div>
      <button
        v-if="showRightScrollButton"
        class="flex h-full flex-shrink-0 items-center justify-center px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
        aria-label="Scroll right"
        @click="scrollMenu('right')"
      >
        <font-awesome-icon :icon="['fas', 'chevron-right']" />
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    required: true,
  },
  currentStepIndex: {
    type: Number,
    required: true,
  },
  selectedOptions: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["jump-to-step"]);

const { cn } = useUtilities();

const { menuRef, showLeftScrollButton, showRightScrollButton, scrollMenu } = useScrollableMenu({
  scrollThreshold: 10,
});
</script>
