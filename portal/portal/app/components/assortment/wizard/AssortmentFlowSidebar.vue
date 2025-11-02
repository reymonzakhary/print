<template>
  <aside
    class="flex h-full w-80 flex-col border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
  >
    <!-- Header -->
    <div class="flex-shrink-0 border-b border-gray-200 p-6 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          <template v-if="categoryName">
            {{ isEditMode ? $t("Edit") : $t("Product Wizard") }}: {{ categoryName }}
          </template>
          <template v-else>
            {{ isEditMode ? $t("Edit Product") : $t("Product Wizard") }}
          </template>
        </h2>
        <div v-if="!isEditMode" class="text-sm text-gray-500 dark:text-gray-400">
          {{ currentStep }}/{{ totalSteps }}
        </div>
      </div>

      <!-- Global Progress Bar (hidden in edit mode) -->
      <div v-if="!isEditMode" class="mt-4">
        <div class="mb-1 flex justify-between text-xs text-gray-600 dark:text-gray-400">
          <span>{{ $t("Progress") }}</span>
          <span>{{ Math.round((completedSteps.length / totalSteps) * 100) }}%</span>
        </div>
        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
          <div
            class="h-2 rounded-full bg-emerald-500 transition-all duration-300"
            :style="{ width: `${(completedSteps.length / totalSteps) * 100}%` }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Step Navigation -->
    <nav class="flex-1 overflow-y-auto p-4">
      <div class="space-y-1">
        <!-- Phase 1: Product Foundation (in edit mode, only show step 3) -->
        <StepPhase
          v-if="foundationStepsForDisplay.length > 0"
          :title="$t('Product Foundation')"
          :steps="foundationStepsForDisplay"
          :current-step="currentStep"
          :completed-steps="isEditMode ? [] : completedSteps"
          :can-proceed-to="canProceedTo"
          @step-selected="$emit('step-selected', $event)"
        >
          <!-- Dynamic Foundation Steps -->
          <template v-for="stepNumber in foundationStepsForDisplay" :key="`foundation-${stepNumber}`">
            <StepItem
              v-if="isStepAvailable(stepNumber)"
              :step-number="stepNumber"
              :title="$t(getStepTitle(stepNumber))"
              :description="$t(getStepDescription(stepNumber))"
              :icon="getStepIcon(stepNumber)"
              :current="currentStep === stepNumber"
              :completed="isEditMode ? false : completedSteps.includes(stepNumber)"
              :accessible="isEditMode ? true : (canProceedTo(stepNumber) && !isStepLocked(stepNumber))"
              :class="{ 'opacity-50': !isEditMode && isStepLocked(stepNumber) }"
              @click="!isStepLocked(stepNumber) && $emit('step-selected', stepNumber)"
            />
          </template>
        </StepPhase>

        <!-- Phase 2: Product Configuration -->
        <StepPhase
          :title="$t('Product Configuration')"
          :steps="configurationSteps"
          :current-step="currentStep"
          :completed-steps="isEditMode ? [] : completedSteps"
          :can-proceed-to="canProceedTo"
          @step-selected="$emit('step-selected', $event)"
        >
          <!-- Dynamic Configuration Steps -->
          <template v-for="stepNumber in configurationSteps" :key="`config-${stepNumber}`">
            <StepItem
              v-if="isStepAvailable(stepNumber)"
              :step-number="stepNumber"
              :title="$t(getStepTitle(stepNumber))"
              :description="$t(getStepDescription(stepNumber))"
              :icon="getStepIcon(stepNumber)"
              :current="currentStep === stepNumber"
              :completed="isEditMode ? false : completedSteps.includes(stepNumber)"
              :accessible="isEditMode ? true : (canProceedTo(stepNumber) && !isStepLocked(stepNumber))"
              :class="{ 'opacity-50': !isEditMode && isStepLocked(stepNumber) }"
              @click="!isStepLocked(stepNumber) && $emit('step-selected', stepNumber)"
            />
          </template>
        </StepPhase>

        <!-- Phase 3: Pricing Strategies -->
        <StepPhase
          v-if="pricingSteps.length > 0"
          :title="$t('Pricing Strategies')"
          :steps="pricingSteps"
          :current-step="currentStep"
          :completed-steps="isEditMode ? [] : completedSteps"
          :can-proceed-to="canProceedTo"
          @step-selected="$emit('step-selected', $event)"
        >
          <!-- Dynamic Pricing Steps -->
          <template v-for="stepNumber in pricingSteps" :key="`pricing-${stepNumber}`">
            <StepItem
              v-if="isStepAvailable(stepNumber)"
              :step-number="stepNumber"
              :title="$t(getStepTitle(stepNumber))"
              :description="$t(getStepDescription(stepNumber))"
              :icon="getStepIcon(stepNumber)"
              :current="currentStep === stepNumber"
              :completed="isEditMode ? false : completedSteps.includes(stepNumber)"
              :accessible="isEditMode ? true : (canProceedTo(stepNumber) && !isStepLocked(stepNumber))"
              :class="{ 'opacity-50': !isEditMode && isStepLocked(stepNumber) }"
              @click="!isStepLocked(stepNumber) && $emit('step-selected', stepNumber)"
            />
          </template>
        </StepPhase>

        <!-- Phase 4: Review & Finalize -->
        <StepPhase
          :title="$t('Review & Finalize')"
          :steps="reviewSteps"
          :current-step="currentStep"
          :completed-steps="isEditMode ? [] : completedSteps"
          :can-proceed-to="canProceedTo"
          @step-selected="$emit('step-selected', $event)"
        >
          <!-- Dynamic Review Steps -->
          <template v-for="stepNumber in reviewSteps" :key="`review-${stepNumber}`">
            <StepItem
              v-if="isStepAvailable(stepNumber)"
              :step-number="stepNumber"
              :title="$t(getStepTitle(stepNumber))"
              :description="$t(getStepDescription(stepNumber))"
              :icon="getStepIcon(stepNumber)"
              :current="currentStep === stepNumber"
              :completed="isEditMode ? false : completedSteps.includes(stepNumber)"
              :accessible="isEditMode ? true : (canProceedTo(stepNumber) && !isStepLocked(stepNumber))"
              :class="{ 'opacity-50': !isEditMode && isStepLocked(stepNumber) }"
              @click="!isStepLocked(stepNumber) && $emit('step-selected', stepNumber)"
            />
          </template>
        </StepPhase>
      </div>
    </nav>

    <!-- Auto-save Status -->
    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
      <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
        <font-awesome-icon
          :icon="['fas', 'cloud']"
          :class="isDirty ? 'text-yellow-500' : 'text-green-500'"
          class="mr-1"
        />
        <span v-if="isDirty">{{ $t("Saving...") }}</span>
        <span v-else-if="lastSaved"> {{ $t("Saved") }} {{ formatTimeAgo(lastSaved) }} </span>
        <span v-else>{{ $t("Not saved") }}</span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from "vue";
import StepPhase from "./shared/StepPhase.vue";
import StepItem from "./shared/StepItem.vue";

const { t: $t } = useI18n();

const props = defineProps({
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
  totalSteps: {
    type: Number,
    required: true,
  },
  lastSaved: {
    type: Date,
    default: null,
  },
  isDirty: {
    type: Boolean,
    default: false,
  },
  // New props for dynamic step structure
  availableSteps: {
    type: Array,
    default: () => [1, 2, 5, 6, 7], // Default standard flow
  },
  isProducerFlow: {
    type: Boolean,
    default: false,
  },
  categoryCreated: {
    type: Boolean,
    default: false,
  },
  isEditMode: {
    type: Boolean,
    default: false,
  },
  categoryName: {
    type: String,
    default: "",
  },
  getStepTitle: {
    type: Function,
    required: true,
  },
  getStepDescription: {
    type: Function,
    required: true,
  },
});

const emit = defineEmits(["step-selected"]);

// Dynamically group steps into phases based on step number and metadata
const foundationSteps = computed(() => {
  // Foundation: steps <= 4 (Overview, Category Search, Category Config, Producer, Producer Category)
  return props.availableSteps.filter((s) => s <= 4);
});

// Foundation steps to display (filtered by edit mode)
const foundationStepsForDisplay = computed(() => {
  if (props.isEditMode) {
    // In edit mode, only show step 3 (Category Configuration)
    return foundationSteps.value.filter((s) => s === 3);
  }
  // In create mode, show all foundation steps
  return foundationSteps.value;
});

const configurationSteps = computed(() => {
  // Configuration: steps 6, 7, 12 (Edit Boxes, Excludes, Calc References)
  return props.availableSteps.filter((s) => (s >= 6 && s <= 7) || s === 12);
});

const reviewSteps = computed(() => {
  // Review: step 8 (Manifest)
  return props.availableSteps.filter((s) => s === 8);
});

const pricingSteps = computed(() => {
  // Pricing Strategies: steps 9-11 (Calculation, Pricing Tables, Margins)
  return props.availableSteps.filter((s) => s >= 9 && s <= 11);
});

// Check if step should be shown based on available steps
const isStepAvailable = (stepNumber) => {
  return props.availableSteps.includes(stepNumber);
};

// Check if step is locked
const isStepLocked = (stepNumber) => {
  // Steps 1-2 are locked after category creation when beyond step 2
  if (props.categoryCreated && [1, 2].includes(stepNumber) && props.currentStep > 2) {
    return true;
  }
  return false;
};

// Get step icon
const getStepIcon = (stepNumber) => {
  const stepIcons = {
    1: "box", // Product Overview
    2: "search", // Category Search
    3: "sliders", // Category Configuration
    4: "user-tie", // Select Producer
    5: "tags", // Producer Category
    6: "cogs", // Edit Boxes & Options
    7: "ban", // Manage Excludes
    8: "clipboard-check", // Product Manifest
    9: "calculator-simple", // Calculation
    10: "table", // Pricing Tables
    11: "percent", // Margins
    12: "link", // Calculation References
  };
  return stepIcons[stepNumber] || "circle";
};

const formatTimeAgo = (date) => {
  if (!date) return "";
  const now = new Date();
  const diff = now - date;
  const minutes = Math.floor(diff / 60000);
  if (minutes < 1) return "now";
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  return `${hours}h ago`;
};
</script>
