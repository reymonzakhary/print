<template>
  <div class="mb-6">
    <!-- Phase Header -->
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
        {{ title }}
      </h3>
      <div class="text-xs text-gray-500 dark:text-gray-400">
        {{ completedPhaseSteps }}/{{ steps.length }}
      </div>
    </div>

    <!-- Phase Progress -->
    <div class="mb-4 h-1 w-full rounded-full bg-gray-200 dark:bg-gray-700">
      <div
        class="h-1 rounded-full bg-theme-500 transition-all duration-300"
        :style="{ width: `${phaseProgress}%` }"
      ></div>
    </div>

    <!-- Phase Steps -->
    <div class="space-y-2">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  steps: {
    type: Array,
    required: true,
  },
  currentStep: {
    type: Number,
    required: true,
  },
  completedSteps: {
    type: Array,
    default: () => [],
  },
  canProceedTo: {
    type: Function,
    required: true,
  },
});

const emit = defineEmits(["step-selected"]);

const completedPhaseSteps = computed(() => {
  return props.steps.filter((step) => props.completedSteps.includes(step)).length;
});

const phaseProgress = computed(() => {
  if (props.steps.length === 0) return 0;
  return Math.round((completedPhaseSteps.value / props.steps.length) * 100);
});
</script>
