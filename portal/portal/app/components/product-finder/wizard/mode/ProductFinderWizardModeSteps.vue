<template>
  <div class="relative">
    <ProductFinderWizardModeStepsNavigation
      :steps="steps"
      :current-step-index="currentStepIndex"
      :selected-options="selectedOptions"
      @jump-to-step="(currentStepIndex = $event) && (isManuallyStepping = true)"
    />
    <h2
      v-if="currentStep?.divider && currentStep?.divider !== '_'"
      class="text-base/7 font-semibold text-gray-500"
    >
      {{ currentStep.divider }}
    </h2>
    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
      {{ $display_name(currentStep?.display_name) }}
    </h3>
    <Transition name="fade">
      <ProductFinderUIBoxInput
        :key="currentStepIndex"
        :box="currentStep"
        :selected-options="selectedOptions"
        :manifest="manifests"
        show-all
        focus-on-mount
        @select-option="handleOptionSelect"
      />
    </Transition>
    <ProductFinderWizardNavButtons
      :current-step-index="currentStepIndex"
      :current-step="currentStep"
      :is-last-step="currentStepIndex === steps.length - 1"
      :disabled="!selectedOptions['quantity']"
      :enable-calculation="enableCalculation"
      :is-current-step-selected="currentStepSelected"
      @previous="previousStep"
      @next="nextStep"
      @start-calculation="emit('start-calculation')"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  selectedOptions: {
    type: Object,
    required: true,
  },
  enableCalculation: {
    type: Boolean,
    default: true,
  },
  manifests: {
    type: Array,
    default: () => [],
  },
});

watch(
  () => props.selectedOptions,
  () => !props.selectedOptions["quantity"] && (currentStepIndex.value = 0),
);

const emit = defineEmits(["select-option", "start", "start-calculation"]);

const steps = computed(() => props.category.boops);
const currentStepIndex = ref(0);
const currentStep = computed(() => steps.value[currentStepIndex.value]);
const isNotQuantity = computed(() => currentStep.value.linked !== "quantity");
const currentStepSelected = computed(() => !!props.selectedOptions[currentStep.value.linked]);

const isManuallyStepping = ref(false);
// Prevents nextStep from being called multiple times in a row.
const isNextStepping = ref(false);
const handleOptionSelect = ({ option }) => {
  emit("select-option", { boop: currentStep.value, option });
  // TODO: Improve logic for showing boop that changed in sidebar.
  if (isNotQuantity.value && !isNextStepping.value && !isManuallyStepping.value) nextStep();
};

const nextStep = () => {
  if (isNextStepping.value) return;
  isNextStepping.value = true;
  if (currentStepIndex.value === steps.value.length - 1) return;
  if (!props.selectedOptions["quantity"]) return;

  isManuallyStepping.value = false;

  let nextStep = currentStepIndex.value + 1;

  // If we're on the first step (quantity), find the next unselected step
  if (currentStepIndex.value === 0) {
    for (let i = 1; i < steps.value.length; i++) {
      const stepLinked = steps.value[i].linked;
      if (!props.selectedOptions[stepLinked]) {
        nextStep = i;
        break;
      }
    }
  }
  currentStepIndex.value = nextStep;
  if (nextStep > 0) emit("start");
  setTimeout(() => {
    isNextStepping.value = false;
  }, 100);
};

const previousStep = () => {
  if (currentStepIndex.value === 0) return;
  currentStepIndex.value--;
  isManuallyStepping.value = true;
};
</script>
