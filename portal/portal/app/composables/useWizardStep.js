/**
 * Composable for handling wizard step logic
 */
import { ref } from "vue";

export const useWizardStep = () => {
  const selectedCategory = ref(null);
  const selectedSearchItem = ref({});
  const name = ref("");
  const displayName = ref("");

  /**
   * Validates the current step
   * @param {Function} emit - The emit function from the component
   * @param {Object} productWizardStore - The product wizard store
   * @returns {boolean} Whether the step is valid
   */
  const validateStep = (emit, productWizardStore) => {
    const isValid = selectedCategory.value && Object.keys(selectedCategory.value).length > 0;

    emit("step-validated", {
      isValid: isValid,
      canProceed: isValid,
      valid: isValid,
      stepNumber: productWizardStore.currentStep,
    });

    return isValid;
  };

  /**
   * Handles category selection with automatic boops loading
   * @param {Object} category - The selected category
   * @param {Function} validateStepFn - The validate step function
   * @param {Object} api - The API instance for fetching boops
   * @param {Object} productWizardStore - The wizard store for persisting data
   */
  const onCategorySelected = async (
    category,
    validateStepFn,
    api = null,
    productWizardStore = null,
  ) => {
    // Set the selected category to show the form
    selectedCategory.value = category;

    // Set default values for the form (user can edit these)
    name.value = category.name;
    displayName.value = category.display_name;

    // If API and store are available, check if we need to load boops data
    if (api && productWizardStore && category.slug) {
      // First, check if we already have boops data from a previous wizard session
      const existingBoops = productWizardStore.getStepData("editBoops");

      if (existingBoops && existingBoops.selectedBoops && existingBoops.selectedBoops.length > 0) {
        // We have existing boops, no need to reload from API
      } else {
        // No existing boops data, load fresh from API
        try {
          const response = await api.get(`/categories/${category.linked}/manifest`);

          // Handle different API response structures
          let boops = [];
          if (response.data.boops && Array.isArray(response.data.boops)) {
            // If boops is an array, take first element's boops property or use as-is
            boops = response.data.boops[0]?.boops || response.data.boops;
          } else if (response.data.boops) {
            // If boops exists but isn't array, use it directly
            boops = response.data.boops;
          }

          // Store the boops data in the wizard for the EditBoops step
          productWizardStore.updateStepData("editBoops", {
            selectedBoops: boops,
            divided: category.divided || false,
            initialized: true,
            lastUpdated: Date.now(),
          });
        } catch (error) {
          console.error("Failed to load boops for category:", category.slug, error);
          // Don't fail the step, just log the error
        }
      }
    }

    // Validate step so user can proceed when ready
    validateStepFn();
  };

  /**
   * Updates display name
   * @param {string} newDisplayName - The new display name
   * @param {Object} productWizardStore - The product wizard store
   */
  const updateDisplayName = (newDisplayName, productWizardStore) => {
    displayName.value = newDisplayName;

    // Update the store as well
    productWizardStore.updateStepData("categorySearch", {
      display_name: newDisplayName,
    });
  };

  /**
   * Updates system key
   * @param {string} newName - The new system key
   */
  const updateName = (newName) => {
    name.value = newName;
  };

  /**
   * Emits step completion events
   * @param {Function} emit - The emit function
   * @param {Object} categoryData - The created category data
   * @param {Function} onComplete - Callback to execute after progression
   */
  const emitStepCompletion = (emit, categoryData, onComplete) => {
    // Validate the step now that we have a category
    const isValid = categoryData && Object.keys(categoryData).length > 0;

    emit("step-validated", {
      isValid: isValid,
      canProceed: isValid,
      valid: isValid,
      stepNumber: 2,
    });

    // Complete the step
    emit("step-completed", {
      stepId: "categorySearch",
      stepNumber: 2,
      data: {
        selected_category: categoryData,
        selected_boops: categoryData.boops,
        display_name: categoryData.display_name,
        name: categoryData.name,
      },
    });

    // Proceed to next step after a short delay
    setTimeout(() => {
      emit("next-step");
      // Execute callback after progression is complete
      if (onComplete) {
        onComplete();
      }
    }, 100);
  };

  /**
   * Handles step validation errors
   * @param {Function} emit - The emit function
   * @param {Object} error - The error object
   */
  const handleStepError = (emit, error) => {
    // For validation errors (422), keep the form available so user can edit and retry
    const isConflictError = error.isConflictError;

    if (isConflictError) {
      // Keep the form valid so user can edit display name and try again
      emit("step-validated", {
        isValid: true,
        canProceed: true,
        valid: true,
        stepNumber: 2,
        canRetry: true,
      });
    } else {
      // For other errors, reset validation state
      emit("step-validated", {
        isValid: false,
        canProceed: false,
        valid: false,
        stepNumber: 2,
        error: error.message,
      });
    }
  };

  /**
   * Loads existing step data from store
   * @param {Object} productWizardStore - The product wizard store
   */
  const loadExistingData = (productWizardStore) => {
    const existingData = productWizardStore.getStepData("categorySearch");
    if (existingData) {
      if (existingData.selected_category) {
        selectedCategory.value = existingData.selected_category;
      }
      if (existingData.selected_search_item) {
        selectedSearchItem.value = existingData.selected_search_item;
      }
      if (existingData.name) {
        name.value = existingData.name;
      }
      if (existingData.display_name) {
        displayName.value = existingData.display_name;
      }
    }
  };

  return {
    selectedCategory,
    selectedSearchItem,
    name,
    displayName,
    validateStep,
    onCategorySelected,
    updateDisplayName,
    updateName,
    emitStepCompletion,
    handleStepError,
    loadExistingData,
  };
};
