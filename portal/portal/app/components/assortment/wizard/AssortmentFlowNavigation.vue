<template>
  <footer class="border-t px-4 py-4 dark:border-gray-800">
    <div class="flex items-center justify-between">
      <!-- Previous button -->
      <button
        @click="$emit('previous')"
        :disabled="!canGoPrevious"
        :class="[
          'flex items-center gap-2 rounded-lg px-6 py-2 font-medium transition-all duration-200',
          canGoPrevious
            ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
            : 'cursor-not-allowed bg-gray-50 text-gray-400 dark:bg-gray-800 dark:text-gray-600',
        ]"
      >
        <font-awesome-icon :icon="['fal', 'arrow-left']" />
        {{ $t("Previous") }}
      </button>

      <!-- Step indicator -->
      <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
        {{ $t("Step {current} of {total}", { current: currentStep, total: totalSteps }) }}
      </div>

      <!-- Next/Save button -->
      <button
        @click="$emit('next')"
        :disabled="!canGoNext"
        :class="[
          'flex items-center gap-2 rounded-lg px-6 py-2 font-medium transition-all duration-200',
          canGoNext
            ? isEditMode
              ? 'bg-green-500 text-white shadow-md hover:bg-green-600 hover:shadow-lg'
              : 'bg-blue-500 text-white shadow-md hover:bg-blue-600 hover:shadow-lg'
            : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600',
        ]"
      >
        {{ isEditMode ? $t("Save") : isLastStep ? $t("Finish") : $t("Next") }}
        <font-awesome-icon :icon="['fal', isEditMode ? 'save' : isLastStep ? 'check' : 'arrow-right']" />
      </button>
    </div>
  </footer>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  currentStep: {
    type: Number,
    default: 1,
  },
  totalSteps: {
    type: Number,
    default: 7,
  },
  canGoNext: {
    type: Boolean,
    default: true,
  },
  isEditMode: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["next", "previous"]);

const canGoPrevious = computed(
  () => (props.currentStep > 1 && props.currentStep < 3) || props.currentStep > 3,
);
const isLastStep = computed(() => props.currentStep >= props.totalSteps);
</script>
