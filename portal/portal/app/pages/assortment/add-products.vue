<template>
  <div class="new_flow flex h-full w-full overflow-hidden pb-2">
    <!-- Step Navigation Sidebar -->
    <AssortmentFlowSidebar
      :current-step="productWizardStore.currentStep"
      :completed-steps="productWizardStore.completedSteps"
      :can-proceed-to="productWizardStore.canProceedTo"
      :total-steps="productWizardStore.dynamicTotalSteps"
      :available-steps="productWizardStore.availableSteps"
      :is-producer-flow="productWizardStore.isProducerFlow"
      :category-created="productWizardStore.categoryCreated"
      :is-edit-mode="productWizardStore.isEditMode"
      :category-name="productWizardStore.categoryName"
      :get-step-title="safeGetStepTitle"
      :get-step-description="safeGetStepDescription"
      @step-selected="productWizardStore.setCurrentStep"
    />

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col">
      <!-- Progress Header -->
      <AssortmentFlowHeader
        :last-saved="productWizardStore.lastSaved ? new Date(productWizardStore.lastSaved) : null"
        :is-dirty="productWizardStore.isDirty"
        @toggle-help="helpVisible = !helpVisible"
        @close-wizard="handleCloseWizard"
      />

      <!-- Step Content -->
      <div class="relative flex flex-1 overflow-hidden">
        <!-- Main content area with navigation -->
        <div
          class="flex flex-1 flex-col overflow-hidden bg-white shadow-lg shadow-gray-300 dark:bg-gray-700 dark:shadow-gray-900"
          :class="{ 'rounded-md': helpVisible, 'rounded-l-md': !helpVisible }"
        >
          <main :class="['w-full flex-1 overflow-y-auto p-4']">
            <transition name="slide-fade" mode="out-in">
              <component
                :is="currentStepComponent"
                ref="currentStepComponentRef"
                :key="productWizardStore.currentStep"
                :help-visible="helpVisible"
                @step-completed="onStepCompleted"
                @step-validated="onStepValidated"
                @next-step="goToNextStep"
                @previous-step="goToPreviousStep"
              />
            </transition>
          </main>

          <!-- Step Navigation Footer -->
          <AssortmentFlowNavigation
            :current-step="productWizardStore.currentStepPosition"
            :total-steps="productWizardStore.dynamicTotalSteps"
            :can-go-next="currentStepValid"
            :is-edit-mode="productWizardStore.isEditMode"
            @next="goToNextStep"
            @previous="goToPreviousStep"
          />
        </div>

        <!-- Contextual Help Panel -->
        <transition name="slide-fade">
          <AssortmentFlowHelp
            v-if="helpVisible"
            :current-step="productWizardStore.currentStep"
            :product-type="productWizardStore.productDefinition.type"
            :pricing-method="productWizardStore.productDefinition.pricingMethod"
            :validation-errors="productWizardStore.validationErrors"
            :product-definition="productWizardStore.productDefinition"
          />
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, markRaw } from "vue";
import { useProductWizardStore } from "@/stores/productWizard";
import { useStore } from "vuex";
import {
  AddProductOverview,
  PrintProductCategorySearch,
  CategoryConfigurationStep,
  PrintProductProducerProducer,
  PrintProductProducerCategory,
  PrintProductEditBoops,
  PrintProductEditBoopsManageExcludes,
  PrintProductManifest,
  PrintProductCalculation,
  PrintProductPricingTables,
  PrintProductMargins,
  PrintProductCalcReferences,
  AddCustomProductCategory,
  CustomProductCategory,
  CustomProductForm,
} from "#components";

// Store
const productWizardStore = useProductWizardStore();
const route = useRoute();

// Data
const helpVisible = ref(false);
const currentStepValid = ref(false);
const currentStepComponentRef = ref(null);

// Safe wrapper functions for store getters to prevent initialization errors
const safeGetStepTitle = (stepNumber) => {
  try {
    return productWizardStore.getStepTitle(stepNumber);
  } catch (error) {
    return `Step ${stepNumber}`;
  }
};

const safeGetStepDescription = (stepNumber) => {
  try {
    return productWizardStore.getStepDescription(stepNumber);
  } catch (error) {
    return `Step ${stepNumber}`;
  }
};

// Head
useHead({
  title: `Add Product | Prindustry Manager`,
});

// Step Components Map - Maps step numbers to actual components
const stepComponents = {
  1: markRaw(AddProductOverview),
  2: markRaw(PrintProductCategorySearch),
  3: markRaw(CategoryConfigurationStep),
  4: markRaw(PrintProductProducerProducer),
  5: markRaw(PrintProductProducerCategory),
  6: markRaw(PrintProductEditBoops),
  7: markRaw(PrintProductEditBoopsManageExcludes),
  8: markRaw(PrintProductManifest),
  9: markRaw(PrintProductCalculation),
  10: markRaw(PrintProductPricingTables),
  11: markRaw(PrintProductMargins),
  12: markRaw(PrintProductCalcReferences),
};

// Computed
const currentStepComponent = computed(() => {
  const component = stepComponents[productWizardStore.currentStep];
  return component || stepComponents[1];
});

// Helper method to get current component reference
const getCurrentStepComponentRef = () => {
  return currentStepComponentRef.value;
};

// Methods
const onStepCompleted = (eventData) => {
  const stepNumber = eventData?.stepNumber || eventData || productWizardStore.currentStep;
  productWizardStore.completeStep(stepNumber);

  // If step data is provided, save it to the store
  if (eventData?.data) {
    productWizardStore.updateStepData(eventData.stepId || `step_${stepNumber}`, eventData.data);
  }
};

const onStepValidated = (validationData) => {
  currentStepValid.value =
    validationData.canProceed || validationData.valid || validationData.isValid;

  // Update store validation if needed
  if (validationData.isValid || validationData.valid) {
    productWizardStore.clearValidationErrors(
      validationData.stepNumber || productWizardStore.currentStep,
    );
  }
};

const goToNextStep = async () => {
  // Only proceed if current step is valid
  if (!currentStepValid.value) {
    return;
  }

  // Check if current step component has a goNext method and call it
  const currentComponent = getCurrentStepComponentRef();
  if (currentComponent?.goNext) {
    currentComponent.goNext();
  } else {
    productWizardStore.goToNextStep();
  }

  // Reset validation for next step
  currentStepValid.value = false;
};

const goToPreviousStep = () => {
  productWizardStore.goToPreviousStep();
  // Reset validation when going back
  currentStepValid.value = false;
};

// Complete wizard when reaching final step
const completeWizardFlow = () => {
  productWizardStore.completeWizard();

  // Redirect to appropriate page
  const router = useRouter();
  router.push("/assortment");
};

// Handle closing the wizard
const handleCloseWizard = () => {
  const router = useRouter();

  // In edit mode, just go back to assortment overview
  if (productWizardStore.isEditMode) {
    productWizardStore.resetWizard();
    router.push("/assortment");
    return;
  }

  // In create mode, confirm before closing if there's unsaved work
  if (productWizardStore.isDirty || productWizardStore.categoryCreated) {
    if (
      confirm("Are you sure you want to close the wizard? Unsaved progress will be kept for later.")
    ) {
      router.push("/assortment");
    }
  } else {
    // No progress made, just go back
    productWizardStore.resetWizard();
    router.push("/assortment");
  }
};

// Auto-save interval
let autoSaveInterval = null;

// Lifecycle
onMounted(async () => {
  /**
   * WIZARD INITIALIZATION LOGIC
   *
   * Three modes of operation:
   * 1. EDIT MODE: ?edit=categorySlug - Load existing category for editing
   * 2. CONTINUE MODE: ?continue=wizardId - Restore saved wizard state
   * 3. FRESH MODE: No query params - Start clean wizard from step 1 and generate new ID
   *
   * This ensures users always get the expected experience:
   * - Clicking "Edit" on a category loads it in the wizard for editing
   * - Clicking "Continue Wizard" resumes where they left off
   * - Clicking "Add Product" starts completely fresh with a new session ID
   * - Page refresh always continues the current wizard session
   */

  const router = useRouter();
  const api = useAPI();
  const editCategorySlug = route.query.edit;
  const continueWizard = route.query.continue;
  const store = useStore();
  store.state.product_wizard.selected_boops = [];

  if (editCategorySlug) {
    // EDIT MODE: Load existing category for editing (no localStorage persistence)
    try {
      const response = await api.get(`categories/${editCategorySlug}`);
      const categoryData = response.data;

      // Extract actual boops array - API returns category.boops[0].boops
      // category.boops is an array with one wrapper object that contains the actual boops array
      const actualBoops = categoryData.boops?.[0]?.boops ?? [];
      const divided = categoryData.boops?.[0]?.divided ?? false;

      console.log(
        `Loaded category for editing: ${categoryData.name} with ${actualBoops.length} boxes`,
      );
      console.log("First box structure:", actualBoops[0]);
      console.log(
        "First box has ops?",
        actualBoops[0]?.ops,
        "length:",
        actualBoops[0]?.ops?.length,
      );

      // Initialize wizard in edit mode with category data
      productWizardStore.initializeEditMode(categoryData.id || categoryData.slug, {
        categorySearch: {
          selectedCategory: categoryData,
          selected_category: categoryData,
        },
        editBoops: {
          selectedBoops: actualBoops,
          divided: divided,
        },
        calcReferences: {
          boops: actualBoops,
        },
      });
    } catch (error) {
      console.error("Failed to load category for editing:", error);
      // Fallback to fresh wizard
      productWizardStore.resetWizard();
      const wizardId = await productWizardStore.initializeNewWizard();
      router.replace({ query: { continue: wizardId } });
    }
  } else if (continueWizard) {
    // CONTINUE MODE: Load existing wizard state
    const result = await productWizardStore.loadWizardState(continueWizard);
    if (!result.success) {
      // Failed to load - start fresh wizard
      productWizardStore.resetWizard();
      const wizardId = await productWizardStore.initializeNewWizard();
      router.replace({ query: { continue: wizardId } });
    }
  } else {
    // FRESH MODE: Start new wizard with unique ID and add to URL
    productWizardStore.resetWizard();
    const wizardId = await productWizardStore.initializeNewWizard();

    // Add wizard ID to URL immediately so refresh continues this session
    router.replace({ query: { continue: wizardId } });

    console.log(`Started new wizard session: ${wizardId}`);
  }

  // Clean up old wizard states on app start
  productWizardStore.cleanupOldWizardStates();

  // Set up auto-save every 30 seconds
  autoSaveInterval = setInterval(() => {
    productWizardStore.autoSave();
  }, 30000);
});

onBeforeUnmount(() => {
  // Clear auto-save interval
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval);
  }

  // Save state before leaving if dirty and category created
  if (productWizardStore.isDirty && productWizardStore.categoryCreated) {
    productWizardStore.saveWizardState();
  }
});
</script>
