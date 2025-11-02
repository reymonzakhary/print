<template>
  <div class="mt-8 flex justify-between">
    <button
      v-if="currentStepIndex > 0 && mode === 'steps'"
      class="flex items-center rounded-full bg-gray-100 px-4 py-2 text-gray-700 transition-all hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
      @click="emit('previous')"
    >
      <font-awesome-icon :icon="['fas', 'arrow-left']" class="mr-2 size-4" />
      {{ $t("Previous") }}
    </button>
    <div v-else />
    <div class="flex gap-2">
      <button
        v-if="mode === 'steps'"
        :disabled="disabled || isLastStep"
        :class="
          cn('flex items-center rounded-full px-4 py-2 text-white transition-all', {
            'bg-gray-300 opacity-50': disabled || isLastStep,
            'bg-theme-400 hover:bg-theme-500': !disabled && !isLastStep,
          })
        "
        @click="emit('next')"
      >
        {{ $t(buttonText) }}
        <font-awesome-icon :icon="buttonIcon" class="ml-2 size-4" />
      </button>

      <div class="relative w-fit" :class="{ 'p-[1px]': enableCalculation }">
        <div
          class="absolute -inset-[1px] z-0 rounded-full"
          :class="{
            'bg-gray-300': !enableCalculation,
            'bg-gradient-to-r from-pink-500 to-purple-500': enableCalculation,
          }"
        />
        <button
          :disabled="!enableCalculation"
          :class="
            cn('relative z-10 flex items-center rounded-full px-4 py-2 text-white transition-all', {
              'bg-gray-300 opacity-50 dark:bg-gray-500': !enableCalculation,
              'bg-gradient-to-r from-pink-400 to-purple-400 transition-colors hover:from-pink-500 hover:to-purple-500':
                enableCalculation,
            })
          "
          @click="emit('start-calculation')"
        >
          {{ $t("Show results") }}
          <font-awesome-icon :icon="['fas', 'calculator-simple']" class="ml-2 size-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  mode: {
    type: String,
    default: "steps",
  },
  currentStepIndex: {
    type: Number,
    default: 0,
  },
  currentStep: {
    type: Object,
    default: () => ({
      id: "quantity",
      name: "Quantity",
      divider: "Quantity",
    }),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  isLastStep: {
    type: Boolean,
    default: false,
  },
  enableCalculation: {
    type: Boolean,
    default: true,
  },
  isCurrentStepSelected: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["previous", "next", "start-calculation"]);

const { cn } = useUtilities();

// Computed property for button text
const buttonText = computed(() => {
  return props.currentStep.id === "quantity"
    ? "Start"
    : props.isCurrentStepSelected
      ? "Next"
      : "skip";
});

// Computed property for button icon
const buttonIcon = computed(() => {
  return props.currentStep.id === "quantity" ? ["fas", "play"] : ["fas", "arrow-right"];
});
</script>
