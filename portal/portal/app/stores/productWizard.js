import { defineStore } from "pinia";

/**
 * Product Wizard Store
 * Manages the multi-step assortment creation wizard flow
 */
export const useProductWizardStore = defineStore("productWizard", {
  state: () => ({
    // Step navigation
    currentStep: 1,
    completedSteps: [],
    totalSteps: 5, // Adjustable based on wizard flow needs
    lockedSteps: [], // Steps that cannot be navigated to after certain actions

    // Wizard UI state
    helpVisible: false,
    isDirty: false,
    lastSaved: null,
    isLoading: false,

    // Validation
    validationErrors: {},

    // Product configuration
    productType: null, // 'print' | 'custom'
    pricingMethod: null, // 'per-option' | 'full-calculation'

    // Step data - flexible structure for any step needs
    stepData: {},

    // Category creation tracking
    categoryCreated: false, // Tracks if category has been created (locks steps 1-2)

    // Edit mode tracking
    isEditMode: false, // True when editing existing category (no localStorage persistence)
    editCategoryId: null, // ID/slug of category being edited
  }),

  getters: {
    /**
     * Get the product origin type from step data
     */
    productOriginType: (state) => {
      const originData = state.stepData.productOrigin;
      return originData?.type || null;
    },

    /**
     * Determine if this is a producer flow (steps 1, 3, 4) or standard flow (steps 1, 2)
     */
    isProducerFlow: (state) => {
      const originType = state.stepData.productOrigin?.type;
      return originType === "producer";
    },

    /**
     * Get the selected category name for display
     */
    categoryName: (state) => {
      const category =
        state.stepData?.categorySearch?.selectedCategory ||
        state.stepData?.categorySearch?.selected_category ||
        state.selected_category;

      if (!category) return "";

      if (Array.isArray(category.display_name) && category.display_name.length > 0) {
        return category.display_name[0].display_name;
      } else if (typeof category.display_name === "string") {
        return category.display_name;
      } else if (category.name) {
        return category.name;
      }

      return "";
    },

    /**
     * Get available steps based on flow type
     * This is the single source of truth for step sequences
     */
    availableSteps() {
      // Consistently include step 3 (Category Configuration) in both flows
      const originType = this.stepData.productOrigin?.type;
      // Producer flow: [1, 3, 4, 5, 6, 12, 7, 8]
      //   - 1: Overview (select producer)
      //   - 3: Category Configuration
      //   - 4: Select Producer
      //   - 5: Producer Category (view producer's category)
      //   - 6: Edit Boxes & Options
      //   - 12: Calculation References
      //   - 7: Manage Excludes
      //   - 8: Product Manifest
      // Preset/Blank flow: [1, 2, 3, 6, 12, 7, 9, 10, 11, 8]
      //   - 1: Overview (select preset/blank)
      //   - 2: Category Search
      //   - 3: Category Configuration
      //   - 6: Edit Boxes & Options (skip steps 4 & 5)
      //   - 12: Calculation References (Product Configuration phase)
      //   - 7: Manage Excludes
      //   - 9: Calculation (Pricing Strategies phase)
      //   - 10: Pricing Tables (Pricing Strategies phase)
      //   - 11: Margins (Pricing Strategies phase)
      //   - 8: Product Manifest (Review & Finalize phase - comes last)
      return originType === "producer"
        ? [1, 3, 4, 5, 6, 12, 7, 8]
        : [1, 2, 3, 6, 12, 7, 9, 10, 11, 8];
    },

    /**
     * Determines which steps can be navigated to
     * Note: Returns a function, so we need to calculate availableSteps inline
     * to avoid getter context issues when passed as props
     */
    canProceedTo: (state) => (stepNumber) => {
      // In edit mode, allow free navigation to all available steps (except steps 1-2, 4-5)
      if (state.isEditMode) {
        const originType = state.stepData?.productOrigin?.type;
        const availableSteps =
          originType === "producer" ? [1, 3, 4, 5, 6, 12, 7, 8] : [1, 2, 3, 6, 12, 7, 9, 10, 11, 8];

        // Allow step 3 (Category Configuration) and all steps after step 5
        // Block steps 1-2 (product type/category search) and 4-5 (producer selection)
        const allowedInEditMode = stepNumber === 3 || stepNumber > 5;
        return availableSteps.includes(stepNumber) && allowedInEditMode;
      }

      // Calculate available steps inline (can't use getters.availableSteps in returned functions)
      const originType = state.stepData?.productOrigin?.type;
      const availableSteps =
        originType === "producer" ? [1, 3, 4, 5, 6, 12, 7, 8] : [1, 2, 3, 6, 12, 7, 9, 10, 11, 8];

      // Check if step is available in current flow
      if (!availableSteps.includes(stepNumber)) {
        return false;
      }

      // Check if step is locked (steps 1-2 after category creation)
      if (state.categoryCreated && [1, 2].includes(stepNumber) && state.currentStep > 2) {
        return false;
      }

      // Can always go to step 1 (unless locked)
      if (stepNumber === 1 && !state.lockedSteps.includes(1)) {
        return true;
      }

      // Can go to any completed step (unless locked)
      if (state.completedSteps.includes(stepNumber) && !state.lockedSteps.includes(stepNumber)) {
        return true;
      }

      // Can go to next available step if current is completed
      const currentAvailableIndex = availableSteps.indexOf(state.currentStep);
      const targetAvailableIndex = availableSteps.indexOf(stepNumber);

      if (
        targetAvailableIndex === currentAvailableIndex + 1 &&
        state.completedSteps.includes(state.currentStep)
      ) {
        return true;
      }

      return false;
    },

    /**
     * Dynamic total steps based on flow type
     */
    dynamicTotalSteps: (state) => {
      const originType = state.stepData?.productOrigin?.type;
      if (originType === "producer") {
        // Producer flow: Steps [1, 3, 4, 5, 6, 12, 7, 8] - highest step is 12
        return 12;
      } else {
        // Standard flow: Steps [1, 2, 3, 6, 12, 7, 9, 10, 11, 8] - highest step is 12
        return 12;
      }
    },

    /**
     * Current step position in the available steps sequence (for UI display)
     * Returns the 1-based index of the current step in the available steps array
     */
    currentStepPosition: (state) => {
      const originType = state.stepData?.productOrigin?.type;
      const availableSteps =
        originType === "producer" ? [1, 3, 4, 5, 6, 12, 7, 8] : [1, 2, 3, 6, 12, 7, 9, 10, 11, 8];
      const position = availableSteps.indexOf(state.currentStep);
      return position !== -1 ? position + 1 : 1;
    },

    /**
     * Total number of steps in the current flow (for UI display)
     * Returns the count of available steps
     */
    totalStepsCount: (state) => {
      const originType = state.stepData?.productOrigin?.type;
      const availableSteps =
        originType === "producer" ? [1, 3, 4, 5, 6, 12, 7, 8] : [1, 2, 3, 6, 12, 7, 9, 10, 11, 8];
      return availableSteps.length;
    },

    /**
     * Product definition for backward compatibility
     */
    productDefinition: (state) => ({
      type: state.productType,
      pricingMethod: state.pricingMethod,
    }),

    /**
     * Get step title for any step number
     * Note: Returns a function, so we need inline metadata access
     * to avoid getter context issues when passed as props
     */
    getStepTitle: () => {
      // Inline step metadata (can't use getters.stepMetadata in returned functions)
      const metadata = {
        1: "Product Overview",
        2: "Category Search",
        3: "Category Configuration",
        4: "Select Producer",
        5: "Producer Category",
        6: "Edit Boxes & Options",
        7: "Manage Excludes",
        8: "Product Manifest",
        9: "Calculation",
        10: "Pricing Tables",
        11: "Margins",
        12: "Calculation References",
      };
      return (stepNumber) => metadata[stepNumber] || `Step ${stepNumber}`;
    },

    /**
     * Current step title key (for i18n translation)
     */
    currentStepTitleKey: (state) => {
      const metadata = {
        1: "Product Overview",
        2: "Category Search",
        3: "Category Configuration",
        4: "Select Producer",
        5: "Producer Category",
        6: "Edit Boxes & Options",
        7: "Manage Excludes",
        8: "Product Manifest",
        9: "Calculation",
        10: "Pricing Tables",
        11: "Margins",
        12: "Calculation References",
      };
      return metadata[state.currentStep] || `Step ${state.currentStep}`;
    },

    /**
     * Current step title with category (computed in component for i18n)
     */
    currentStepTitleWithCategory: (state) => {
      const metadata = {
        1: "Product Overview",
        2: "Category Search",
        3: "Category Configuration",
        4: "Select Producer",
        5: "Producer Category",
        6: "Edit Boxes & Options",
        7: "Manage Excludes",
        8: "Product Manifest",
        9: "Calculation",
        10: "Pricing Tables",
        11: "Margins",
        12: "Calculation References",
      };
      let title = metadata[state.currentStep] || `Step ${state.currentStep}`;

      // Get category name for all steps after step 2 (after category selection)
      if (state.currentStep > 2) {
        const category =
          state.stepData?.categorySearch?.selectedCategory ||
          state.stepData?.categorySearch?.selected_category ||
          state.selected_category;
        let catName = "";
        if (category) {
          if (Array.isArray(category.display_name) && category.display_name.length > 0) {
            catName = category.display_name[0].display_name;
          } else if (typeof category.display_name === "string") {
            catName = category.display_name;
          } else if (category.name) {
            catName = category.name;
          }
        }
        if (catName) {
          title = `${catName} - ` + title;
        }
      }
      return title;
    },

    /**
     * Get step description for any step number
     * Note: Returns a function, so we need inline metadata access
     * to avoid getter context issues when passed as props
     *
     * These strings are used as i18n keys (English strings as keys pattern)
     */
    getStepDescription: (state) => {
      // Inline step metadata (English strings used as i18n keys)
      const metadata = {
        1: "Choose your starting point",
        2: "Search and select category",
        3: "Configure category details",
        4: "Choose your producer",
        5: "Select producer category",
        6: "Configure product variations",
        7: "Set option exclusions",
        8: "Final validation and review",
        9: "Set up pricing calculation methods",
        10: "Configure tiered pricing tables",
        11: "Define profit margins and markup",
        12: "Assign calculation references to boxes and options",
      };

      return (stepNumber) => {
        const baseDescription = metadata[stepNumber] || `Step ${stepNumber}`;

        // Dynamic descriptions based on flow
        const originType = state.stepData?.productOrigin?.type;
        if (stepNumber === 4 && originType === "producer") {
          return "Choose your producer (Step 2 of Product Foundation)";
        }
        if (stepNumber === 5 && originType === "producer") {
          return "Select producer category (Step 3 of Product Foundation)";
        }

        return baseDescription;
      };
    },

    /**
     * Current sub-step number
     */
    currentSubStepNumber: (state) => {
      const stepKey = `step${state.currentStep}`;
      const stepProgress = state.stepData[stepKey]?.progress || {};

      // Add progress config for step 3 (Category Config)
      const progressConfig = {
        1: { required: ["productOrigin"], total: 1 },
        2: { required: ["selectedCategory"], total: 1 },
        3: { required: ["editCategory", "categoryConfig"], total: 2 },
        4: { required: ["selectedProducer"], total: 1 },
        5: { required: ["producerCategory"], total: 1 },
        6: { required: ["boxesConfigured", "optionsConfigured"], total: 2 },
        7: { required: ["excludesConfigured"], total: 1 },
        8: { required: ["manifestReviewed", "validationPassed"], total: 2 },
      };

      const config = progressConfig[state.currentStep];
      if (!config) return 1;

      // Count completed requirements
      const completed = config.required.filter((req) => {
        if (stepProgress[req]) return true;
        const generalStepData =
          state.stepData[
            Object.keys(state.stepData).find(
              (key) => state.stepData[key] && state.stepData[key][req],
            )
          ];
        return generalStepData && generalStepData[req];
      }).length;

      return Math.min(completed + 1, config.total); // Current step (1-based)
    },

    /**
     * Total sub-steps in current step
     */
    totalSubStepsInCurrentStep: (state) => {
      // Add progress config for step 3 (Category Config)
      const progressConfig = {
        1: { total: 1 },
        2: { total: 1 },
        3: { total: 2 },
        4: { total: 1 },
        5: { total: 1 },
        6: { total: 2 },
        7: { total: 1 },
        8: { total: 2 },
      };
      const config = progressConfig[state.currentStep];
      return config?.total || 1;
    },

    /**
     * Current step progress percentage
     */
    currentStepProgressPercentage: (state) => {
      const stepKey = `step${state.currentStep}`;
      const stepProgress = state.stepData[stepKey]?.progress || {};

      // Add progress config for step 3 (Category Config)
      const progressConfig = {
        1: { required: ["productOrigin"], total: 1 },
        2: { required: ["selectedCategory"], total: 1 },
        3: { required: ["editCategory", "categoryConfig"], total: 2 },
        4: { required: ["selectedProducer"], total: 1 },
        5: { required: ["producerCategory"], total: 1 },
        6: { required: ["boxesConfigured", "optionsConfigured"], total: 2 },
        7: { required: ["excludesConfigured"], total: 1 },
        8: { required: ["manifestReviewed", "validationPassed"], total: 2 },
      };

      const config = progressConfig[state.currentStep];
      if (!config) return 0;

      // Count completed requirements
      const completed = config.required.filter((req) => {
        // Check in step progress data
        if (stepProgress[req]) return true;

        // Check in general step data
        const generalStepData =
          state.stepData[
            Object.keys(state.stepData).find(
              (key) => state.stepData[key] && state.stepData[key][req],
            )
          ];
        return generalStepData && generalStepData[req];
      }).length;

      return Math.round((completed / config.total) * 100);
    },
    /**
     * When a preset is selected, reset producer category and related data
     */
    resetProducerCategory() {
      // Remove producer category and related step data
      if (this.stepData.step5) delete this.stepData.step5;
      if (this.stepData.step4) delete this.stepData.step4;
      // Optionally reset completed steps
      this.completedSteps = this.completedSteps.filter((s) => s !== 4 && s !== 5);
    },
  },

  actions: {
    /**
     * Navigate to specific step (legacy method - use goToStep instead)
     */
    setCurrentStep(stepNumber) {
      this.goToStep(stepNumber);
    },

    /**
     * Mark step as completed
     */
    completeStep(stepNumber) {
      if (!this.completedSteps.includes(stepNumber)) {
        this.completedSteps.push(stepNumber);
        this.completedSteps.sort((a, b) => a - b);
      }
      this.isDirty = true;
    },

    /**
     * Update product type
     */
    setProductType(type) {
      this.productType = type;
      this.isDirty = true;
    },

    /**
     * Update pricing method
     */
    setPricingMethod(method) {
      this.pricingMethod = method;
      this.isDirty = true;
    },

    /**
     * Update step data
     */
    updateStepData(stepKey, data) {
      this.stepData[stepKey] = {
        ...this.stepData[stepKey],
        ...data,
      };
      this.isDirty = true;
    },

    /**
     * Update step progress
     */
    updateStepProgress(stepNumber, progressKey, value) {
      const stepKey = `step${stepNumber}`;
      if (!this.stepData[stepKey]) {
        this.stepData[stepKey] = {};
      }
      if (!this.stepData[stepKey].progress) {
        this.stepData[stepKey].progress = {};
      }

      this.stepData[stepKey].progress[progressKey] = value;
      this.isDirty = true;
    },

    /**
     * Get step data
     */
    getStepData(stepKey) {
      return this.stepData[stepKey] || {};
    },

    /**
     * Set validation errors
     */
    setValidationErrors(stepNumber, errors) {
      this.validationErrors[`step${stepNumber}`] = errors;
    },

    /**
     * Clear validation errors
     */
    clearValidationErrors(stepNumber) {
      if (stepNumber) {
        delete this.validationErrors[`step${stepNumber}`];
      } else {
        this.validationErrors = {};
      }
    },

    /**
     * Toggle help panel
     */
    toggleHelp() {
      this.helpVisible = !this.helpVisible;
    },

    /**
     * Save wizard state to localStorage
     */
    async saveWizardState(categoryId = null) {
      // Skip saving in edit mode (no localStorage persistence)
      if (this.isEditMode) {
        console.log("Skipping wizard save in edit mode");
        return { success: true, skipped: true };
      }

      this.isLoading = true;
      try {
        // Generate unique identifier for this wizard session
        const wizardId =
          categoryId ||
          this.stepData?.categorySearch?.selected_category?.id ||
          this.stepData?.categorySearch?.selected_category?.slug ||
          `wizard_${Date.now()}`;

        const wizardState = {
          id: wizardId,
          currentStep: this.currentStep,
          completedSteps: this.completedSteps,
          lockedSteps: this.lockedSteps,
          categoryCreated: this.categoryCreated,
          productType: this.productType,
          pricingMethod: this.pricingMethod,
          stepData: this.stepData,
          createdAt: new Date().toISOString(),
          lastSaved: new Date().toISOString(),
        };

        // Save to localStorage
        const storageKey = `wizard_state_${wizardId}`;
        localStorage.setItem(storageKey, JSON.stringify(wizardState));

        // Extract category information for better matching
        const selectedCategory = this.stepData?.categorySearch?.selected_category;
        const extractedCategoryId = selectedCategory?.id || null;
        const extractedCategorySlug = selectedCategory?.slug || null;
        const extractedCategoryName =
          selectedCategory?.display_name ||
          this.stepData?.categorySearch?.name ||
          "Unnamed Category";

        // Also maintain a list of active wizard states
        const activeWizards = JSON.parse(localStorage.getItem("active_wizards") || "[]");
        const existingWizardIndex = activeWizards.findIndex((w) => w.id === wizardId);

        const wizardMetadata = {
          id: wizardId,
          categoryId: extractedCategoryId, // Store the actual category ID separately
          categorySlug: extractedCategorySlug, // Store the category slug separately
          categoryName: extractedCategoryName,
          createdAt: wizardState.createdAt,
          lastSaved: wizardState.lastSaved,
          currentStep: this.currentStep,
          productType: this.productType,
        };

        if (existingWizardIndex >= 0) {
          // Update existing wizard metadata
          activeWizards[existingWizardIndex] = wizardMetadata;
        } else {
          // Add new wizard metadata
          activeWizards.push(wizardMetadata);
        }
        localStorage.setItem("active_wizards", JSON.stringify(activeWizards));

        this.lastSaved = wizardState.lastSaved;
        this.isDirty = false;

        console.log(`ProductWizard - State saved to localStorage with ID: ${wizardId}`, {
          categoryId: extractedCategoryId,
          categorySlug: extractedCategorySlug,
          categoryName: extractedCategoryName,
          currentStep: this.currentStep,
        });
        return { success: true, wizardId };
      } catch (error) {
        console.error("Failed to save wizard state:", error);
        return { success: false, error };
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Load wizard state from localStorage
     */
    async loadWizardState(wizardId) {
      this.isLoading = true;
      try {
        const storageKey = `wizard_state_${wizardId}`;
        const savedState = localStorage.getItem(storageKey);

        if (!savedState) {
          console.log(`ProductWizard - No saved state found for ID: ${wizardId}`);
          return { success: false, reason: "not_found" };
        }

        const wizardState = JSON.parse(savedState);

        // Restore wizard state
        this.currentStep = wizardState.currentStep;
        this.completedSteps = wizardState.completedSteps || [];
        this.lockedSteps = wizardState.lockedSteps || [];
        this.categoryCreated = wizardState.categoryCreated || false;
        this.productType = wizardState.productType;
        this.pricingMethod = wizardState.pricingMethod;
        this.stepData = wizardState.stepData || {};
        this.lastSaved = wizardState.lastSaved;
        this.isDirty = false;

        return { success: true, wizardState };
      } catch (error) {
        console.error("Failed to load wizard state:", error);
        return { success: false, error };
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Initialize edit mode with existing category data
     * Pre-populates wizard without creating active wizard state
     *
     * @param {string} categoryId - Category identifier
     * @param {Object} data - Pre-populated step data
     */
    initializeEditMode(categoryId, data = {}) {
      // Reset wizard to clean state
      this.resetWizard();

      // Set to edit mode (no localStorage persistence)
      this.isEditMode = true;
      this.editCategoryId = categoryId;

      // Pre-populate step data
      Object.keys(data).forEach((stepKey) => {
        this.stepData[stepKey] = data[stepKey];
      });

      // Start at step 3 (Category Configuration) for editing
      // This is the first editable step in edit mode
      this.currentStep = 3;
      this.completedSteps = [1, 2]; // Mark product type/category search as completed

      // Don't save to localStorage in edit mode
      this.isDirty = false;

      console.log("Initialized edit mode for category:", categoryId, "with data:", data);
    },

    /**
     * Mark category as created (locks steps 1-2)
     */
    setCategoryCreated() {
      this.categoryCreated = true;
      this.isDirty = true;
    },

    /**
     * Lock specific steps
     */
    lockSteps(stepNumbers) {
      stepNumbers.forEach((stepNumber) => {
        if (!this.lockedSteps.includes(stepNumber)) {
          this.lockedSteps.push(stepNumber);
        }
      });
      this.isDirty = true;
    },

    /**
     * Unlock specific steps
     */
    unlockSteps(stepNumbers) {
      stepNumbers.forEach((stepNumber) => {
        const index = this.lockedSteps.indexOf(stepNumber);
        if (index > -1) {
          this.lockedSteps.splice(index, 1);
        }
      });
      this.isDirty = true;
    },

    /**
     * Navigate to step (respects dynamic structure and locking)
     */
    goToStep(stepNumber) {
      if (this.canProceedTo(stepNumber) && this.availableSteps.includes(stepNumber)) {
        this.currentStep = stepNumber;
        console.log(`ProductWizard - Navigated to step ${stepNumber}`);
      } else {
        console.warn(
          `ProductWizard - Cannot navigate to step ${stepNumber}. Available steps:`,
          this.availableSteps,
          "Can proceed:",
          this.canProceedTo(stepNumber),
        );
      }
    },

    /**
     * Go to next available step in the flow
     */
    goToNextStep() {
      const currentIndex = this.availableSteps.indexOf(this.currentStep);
      if (currentIndex !== -1 && currentIndex < this.availableSteps.length - 1) {
        const nextStep = this.availableSteps[currentIndex + 1];
        if (this.completedSteps.includes(this.currentStep)) {
          this.goToStep(nextStep);
        }
      }
    },

    /**
     * Go to previous available step in the flow
     */
    goToPreviousStep() {
      const currentIndex = this.availableSteps.indexOf(this.currentStep);
      if (currentIndex > 0) {
        const prevStep = this.availableSteps[currentIndex - 1];
        this.goToStep(prevStep);
      }
    },

    /**
     * Get all active wizard states from localStorage
     */
    getActiveWizardStates() {
      try {
        const activeWizards = JSON.parse(localStorage.getItem("active_wizards") || "[]");
        return activeWizards.filter((wizard) => {
          // Check if the actual wizard state still exists
          const storageKey = `wizard_state_${wizard.id}`;
          return localStorage.getItem(storageKey) !== null;
        });
      } catch (error) {
        console.error("Failed to get active wizard states:", error);
        return [];
      }
    },

    /**
     * Delete a wizard state from localStorage
     */
    deleteWizardState(wizardId) {
      try {
        // Remove the wizard state itself
        const storageKey = `wizard_state_${wizardId}`;
        localStorage.removeItem(storageKey);

        // Remove from active wizards list
        const activeWizards = JSON.parse(localStorage.getItem("active_wizards") || "[]");
        const filteredWizards = activeWizards.filter((w) => w.id !== wizardId);
        localStorage.setItem("active_wizards", JSON.stringify(filteredWizards));

        console.log(`ProductWizard - Deleted wizard state: ${wizardId}`);
        return { success: true };
      } catch (error) {
        console.error("Failed to delete wizard state:", error);
        return { success: false, error };
      }
    },

    /**
     * Complete wizard and cleanup localStorage
     */
    completeWizard(wizardIdOrCategoryIdentifier = null) {
      // Try to find the actual wizard ID
      let actualWizardId = null;

      if (wizardIdOrCategoryIdentifier) {
        // First, check if this is already a wizard ID (starts with "wizard_")
        if (wizardIdOrCategoryIdentifier.toString().startsWith("wizard_")) {
          actualWizardId = wizardIdOrCategoryIdentifier;
        } else {
          // Otherwise, it's a category ID or slug - find the wizard by category
          const activeWizards = this.getActiveWizardStates();
          const matchingWizard = activeWizards.find(
            (wizard) =>
              wizard.categoryId === wizardIdOrCategoryIdentifier ||
              wizard.categorySlug === wizardIdOrCategoryIdentifier ||
              wizard.id === wizardIdOrCategoryIdentifier,
          );
          if (matchingWizard) {
            actualWizardId = matchingWizard.id;
          }
        }
      } else {
        // Fallback: try to get from current wizard state
        const categoryId = this.stepData?.categorySearch?.selected_category?.id;
        const categorySlug = this.stepData?.categorySearch?.selected_category?.slug;

        if (categoryId || categorySlug) {
          const activeWizards = this.getActiveWizardStates();
          const matchingWizard = activeWizards.find(
            (wizard) => wizard.categoryId === categoryId || wizard.categorySlug === categorySlug,
          );
          if (matchingWizard) {
            actualWizardId = matchingWizard.id;
          }
        }
      }

      if (actualWizardId) {
        this.deleteWizardState(actualWizardId);
        console.log(`ProductWizard - Wizard completed, cleaned up state for: ${actualWizardId}`);
      } else {
        console.warn("ProductWizard - Could not find wizard to complete", {
          input: wizardIdOrCategoryIdentifier,
          stepData: this.stepData,
        });
      }

      this.resetWizard();
    },

    /**
     * Clean up old wizard states (older than 7 days)
     */
    cleanupOldWizardStates() {
      try {
        const activeWizards = JSON.parse(localStorage.getItem("active_wizards") || "[]");
        const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);

        const validWizards = activeWizards.filter((wizard) => {
          const wizardDate = new Date(wizard.createdAt);
          if (wizardDate < sevenDaysAgo) {
            // Remove old wizard state
            this.deleteWizardState(wizard.id);
            console.log(`ProductWizard - Cleaned up old wizard state: ${wizard.id}`);
            return false;
          }
          return true;
        });

        if (validWizards.length !== activeWizards.length) {
          localStorage.setItem("active_wizards", JSON.stringify(validWizards));
        }

        return { success: true, cleaned: activeWizards.length - validWizards.length };
      } catch (error) {
        console.error("Failed to cleanup old wizard states:", error);
        return { success: false, error };
      }
    },

    /**
     * Auto-save wizard state (called periodically)
     */
    async autoSave() {
      if (this.isDirty && this.categoryCreated) {
        console.log("ProductWizard - Auto-saving wizard state...");
        await this.saveWizardState();
      }
    },

    /**
     * Reset wizard
     */
    resetWizard() {
      this.currentStep = 1;
      this.completedSteps = [];
      this.lockedSteps = [];
      this.categoryCreated = false;
      this.productType = null;
      this.pricingMethod = null;
      this.isDirty = false;
      this.lastSaved = null;
      this.validationErrors = {};
      this.stepData = {};
      this.helpVisible = false;
      this.isEditMode = false;
      this.editCategoryId = null;
    },

    /**
     * Initialize a new wizard session with a unique ID
     * Generates a wizard ID and immediately saves initial state
     * Returns the wizard ID to be added to the URL
     */
    async initializeNewWizard() {
      // Generate unique wizard ID
      const wizardId = `wizard_${Date.now()}`;

      // Use existing saveWizardState method with the new ID
      const result = await this.saveWizardState(wizardId);

      if (result.success) {
        console.log(`ProductWizard - New wizard initialized with ID: ${wizardId}`);
        return wizardId;
      } else {
        console.error("Failed to initialize new wizard:", result.error);
        throw new Error("Failed to initialize wizard");
      }
    },
  },
});
