<template>
  <div class="h-full overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Margins") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Define profit margins and markup for your product") }}
      </p>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-6xl">
      <MarginsPage
        ref="marginsPageRef"
        :category="selectedCategory.slug || 'general'"
        :wizard-mode="true"
        @margins-saved="onMarginsSaved"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useStore } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";

const { t: $t } = useI18n();
const { handleError, handleSuccess } = useMessageHandler();
const emit = defineEmits(["step-completed", "step-validated"]);

// Store access (both Vuex and Pinia for backwards compatibility)
const vuexStore = useStore();
const productWizardStore = useProductWizardStore();

// Ref to MarginsPage component
const marginsPageRef = ref(null);
const loading = ref(false);

// Get category from either store
const selectedCategory = computed(() => {
  // Try new Pinia store first
  const piniaCategory =
    productWizardStore.stepData?.categorySearch?.selected_category ||
    productWizardStore.selected_category;

  // Fallback to Vuex store
  const vuexCategory = vuexStore.state.product_wizard?.selected_category;

  return piniaCategory || vuexCategory || {};
});

// Handle margins saved event
const onMarginsSaved = (data) => {
  // Store margin data in wizard store
  productWizardStore.updateStepData("margins", {
    margins: data.margins,
    mode: data.mode,
    lastUpdated: Date.now(),
    saved: true,
  });

  handleSuccess({ message: $t("Margins saved successfully") });

  // Complete the step
  emit("step-completed", {
    stepNumber: 11,
    data: data,
  });

  // Navigate to next step
  productWizardStore.goToNextStep();
};

// Validation
const validateStep = () => {
  // Always valid - margins can be empty or configured
  const isValid = true;

  emit("step-validated", {
    stepNumber: 11,
    isValid,
    canProceed: isValid,
    loading: loading.value,
  });

  return isValid;
};

// Auto-validate on mount
onMounted(() => {
  validateStep();
});

// Watch for changes to auto-validate
watch(
  () => selectedCategory.value,
  () => {
    validateStep();
  },
  { deep: true },
);

// Method called by parent when Next is clicked
async function goNext() {
  if (!validateStep() || !marginsPageRef.value) {
    return;
  }

  loading.value = true;

  try {
    // Call the save function from MarginsPage
    await marginsPageRef.value.handleSaveMargins();
    // onMarginsSaved will handle the rest
  } catch (error) {
    console.error("Error saving margins:", error);
    handleError(error);
  } finally {
    loading.value = false;
    validateStep();
  }
}

defineExpose({ goNext });
</script>
