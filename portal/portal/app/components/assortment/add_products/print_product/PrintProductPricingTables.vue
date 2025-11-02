<template>
  <div class="h-full overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Pricing Tables") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Configure tiered pricing tables for your product") }}
      </p>
    </div>

    <!-- Main Content -->
    <div class="mx-auto w-full">
      <CategoryRanges
        ref="categoryRangesRef"
        :category-ranges="selectedCategory.ranges || []"
        :category-range-list="selectedCategory.range_list || []"
        :category-limits="selectedCategory.limits || []"
        :category-free-entry="selectedCategory.free_entry || []"
        :category-range-around="selectedCategory.range_around || 10"
        :wizard-mode="true"
        @on-update-ranges="onUpdateRanges"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useStore } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";
import CategoryRanges from "@/components/assortment/overview/edit/edit_categories/CategoryRanges.vue";

const { t: $t } = useI18n();
const api = useAPI();
const { handleSuccess } = useMessageHandler();
const emit = defineEmits(["step-completed", "step-validated"]);

// Store access (both Vuex and Pinia for backwards compatibility)
const vuexStore = useStore();
const productWizardStore = useProductWizardStore();

// Ref to CategoryRanges component
const categoryRangesRef = ref(null);

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

// Handle range updates from CategoryRanges
const onUpdateRanges = (data) => {
  // Update the category with the new range data
  const category = selectedCategory.value;
  if (category) {
    category.ranges = data.ranges;
    category.range_list = data.rangeList;
    category.limits = data.limits;
    category.free_entry = data.freeEntry;
    category.range_around = data.rangeAround;

    // Also save to step data
    productWizardStore.updateStepData("pricingTables", {
      ranges: data.ranges,
      range_list: data.rangeList,
      limits: data.limits,
      free_entry: data.freeEntry,
      range_around: data.rangeAround,
    });
  }
  validateStep();
};

// Validation
const validateStep = () => {
  // Pricing tables are optional, so always valid
  // User can skip this step if they don't want to configure ranges
  const isValid = true;

  emit("step-validated", {
    stepNumber: 10,
    isValid,
    canProceed: isValid,
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
  if (validateStep()) {
    // Get the current data from CategoryRanges component
    const rangesData = categoryRangesRef.value;
    const pricingData = {
      ranges: rangesData?.ranges || selectedCategory.value.ranges || [],
      range_list: rangesData?.rangeList || selectedCategory.value.range_list || [],
      limits: rangesData?.limits || selectedCategory.value.limits || [],
      free_entry: rangesData?.freeEntry || selectedCategory.value.free_entry || [],
      range_around: rangesData?.rangeAround || selectedCategory.value.range_around || 10,
    };

    // Update the category object with the latest data
    const category = selectedCategory.value;
    if (category) {
      category.ranges = pricingData.ranges;
      category.range_list = pricingData.range_list;
      category.limits = pricingData.limits;
      category.free_entry = pricingData.free_entry;
      category.range_around = pricingData.range_around;
    }

    // Save to step data
    productWizardStore.updateStepData("pricingTables", pricingData);

    await api.put(`categories/${selectedCategory.value.slug}`, selectedCategory.value);
    handleSuccess({ message: "Price Tables saved successfully" });

    emit("step-completed", {
      stepNumber: 10,
      data: {
        pricingTables: pricingData,
      },
    });
    productWizardStore.goToNextStep();
  }
}

defineExpose({ goNext });
</script>
