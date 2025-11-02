<template>
  <div>
    <div v-if="!props.showProducersToConnect" class="relative isolate h-full">
      <ProductFinderUIBackground />
      <Transition name="fading">
        <ProductFinderWizardHeader v-if="!hideHeader" ref="initialHeader" />
      </Transition>
      <section
        :class="[
          'relative space-y-6',
          { 'position-transition-animation mt-0': hideHeader, 'mt-6': !hideHeader },
        ]"
        :style="{ '--initial-header-height': `${initialHeaderHeight}px` }"
      >
        <ProductFinderWizardModeSwitch :mode="mode" @mode-change="handleModeChange" />
        <section class="mx-auto max-w-2xl 2xl:max-w-3xl">
          <ProductFinderSection variant="primary">
            <ProductFinderWizardModeHeader
              :category="category"
              :calculated-variation-count="calculatedVariationCount"
              :calculated-all-variations-count="calculatedAllVariationsCount"
              :variation-limit="variationLimit"
            />
            <Transition name="fading" mode="out-in">
              <ProductFinderWizardModeSteps
                v-if="mode === 'steps'"
                :category="category"
                :selected-options="selectedOptions"
                :enable-calculation="calculatedVariationCount <= variationLimit"
                :manifests="manifests"
                @select-option="handleOptionSelect"
                @start="hideHeader = true"
                @start-calculation="emit('start-calculation')"
              />
              <ProductFinderWizardModeAll
                v-else-if="mode === 'all'"
                :category="category"
                :selected-options="selectedOptions"
                :manifests="manifests"
                :enable-calculation="calculatedVariationCount <= variationLimit"
                @select-option="handleOptionSelect"
                @start-calculation="emit('start-calculation')"
              />
            </Transition>
          </ProductFinderSection>
        </section>
      </section>
    </div>

    <Transition name="fading">
      <ProductFinderProducersConnect
        v-if="props.showProducersToConnect"
        :category="category"
        :producers="category?.properties_manifest"
        @close="emit('close-producers-connect')"
      />
    </Transition>
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
  calculatedVariationCount: {
    type: Number,
    required: true,
  },
  variationLimit: {
    type: Number,
    required: true,
  },
  calculatedAllVariationsCount: {
    type: Number,
    required: true,
  },
  showProducersToConnect: {
    type: Boolean,
    default: false,
  },
  manifests: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["select-option", "start-calculation"]);

const mode = ref(window.localStorage.getItem("productFinderWizardPreference") || "steps");
watch(mode, (newValue) => window.localStorage.setItem("productFinderWizardPreference", newValue));
watch(
  () => props.showProducersToConnect,
  (newValue) => {
    if (newValue) {
      console.log("Producers to connect shown");
    }
  },
);

const handleOptionSelect = ({ boop, option }) => emit("select-option", { boop, option });

const hideHeader = ref(false);
const handleModeChange = (newMode) => {
  mode.value = newMode;
  hideHeader.value = true;
};
watch(
  () => props.selectedOptions,
  () => {
    if (Object.keys(props.selectedOptions).length >= 1) {
      hideHeader.value = true;
    }
  },
);

/**
 * Header animation
 */
const initialHeader = ref(null);
const initialHeaderHeight = ref(0);
watch(
  initialHeader,
  () => {
    if (initialHeader.value?.$el.offsetHeight) {
      initialHeaderHeight.value = initialHeader.value?.$el.offsetHeight;
    }
  },
  { immediate: true },
);
</script>

<style scoped>
.position-transition-animation {
  animation: position-transition 310ms ease;
}

@keyframes position-transition {
  from {
    position: relative;
    transform: translateY(0px);
  }
  to {
    position: relative;
    transform: translateY(calc(var(--initial-header-height) * -1));
  }
}
</style>
