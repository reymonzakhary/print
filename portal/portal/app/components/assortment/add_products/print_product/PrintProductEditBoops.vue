<template>
  <div class="mx-auto w-full max-w-5xl p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Edit Boxes & Options") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Configure the boxes and options that define your product's variations") }}
      </p>
    </div>

    <div
      class="relative m-4 mx-auto flex w-full items-center justify-between rounded border bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-750"
    >
      <label for="divided" class="font-bold">
        <font-awesome-icon :icon="['fal', 'split']" class="fa-fw mr-2 text-gray-500" />
        {{ $t("use dividers") }}
        <small class="block font-normal">
          {{ $t("enable to group boxes for separate calculations") }}
        </small>
      </label>

      <div v-if="localDivided" class="">
        <UIButton
          :class="{ '!bg-green-500 !text-white': manageDivider }"
          class="shadow"
          variant="theme"
          @click="manageDivider = !manageDivider"
        >
          <font-awesome-icon :icon="['fal', 'pencil']" class="fa-fw" />
          <font-awesome-icon :icon="['fal', 'split']" class="fa-fw mr-2" />
          {{
            manageDivider
              ? capitalizeFirstLetter($t("done managing divider"))
              : capitalizeFirstLetter($t("manage divider"))
          }}
        </UIButton>
      </div>

      <div class="flex items-center" v-if="!manageDivider">
        <UISwitch :value="localDivided" name="divided" @input="onDividedChange($event)" />
        <font-awesome-icon
          v-tooltip="
            //prettier-ignore
            $t('when enabling this toggle you will be able to add boxes to a divider group to have them seperately calculated. For example: a book with a cover and the pages within')
          "
          :icon="['fal', 'circle-info']"
          class="fa-fw ml-2"
        />
      </div>
    </div>

    <div v-if="manageDivider" class="mx-auto">
      <ManageBoopsDivider :selected-boops="selected_boops" />
    </div>

    <EditBoops
      v-if="!manageDivider"
      :editable="type && type === 'from_producer' ? false : true"
      :divided="localDivided"
      :selected-boops="selected_boops"
      :selected-category="selected_category"
      :wizard-mode="true"
      @ordering="ordering = true"
      @done-ordering="ordering = false"
      @boops-updated="onBoopsUpdated"
    />
  </div>
</template>

<script>
/*
 * PrintProductEditBoops - Wizard Step: Edit Boxes & Options
 *
 * 🔥 CRITICAL FOR DEVELOPERS:
 * - This component uses DUAL STORES (VueX + Pinia) for backward compatibility
 * - ALWAYS update both stores when modifying data (see set_selected_boops example)
 * - Use computed properties (selected_boops), NOT vuex_selected_boops directly
 * - Wizard continuation works via localStorage → Pinia store → computed properties
 *
 * Key Integration Points:
 * - validateStep(): Controls Next button (emits step-validated)
 * - goNext(): Called by wizard navigation (triggers updateBoops)
 * - onBoopsUpdated(): Handles EditBoops component changes
 */
import mixin from "~/components/assortment/add_products/customProductMixin.js";
import { mapState, mapMutations } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";

export default {
  mixins: [mixin],
  emits: ["dividedSelected", "step-validated"],
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    const productWizardStore = useProductWizardStore();
    return { capitalizeFirstLetter, api, handleError, handleSuccess, productWizardStore };
  },
  data() {
    return {
      ordering: false,
      localDivided: false,
      manageDivider: false,
      loading: false,
    };
  },
  computed: {
    // Legacy VueX store - DO NOT use these directly, use the computed properties below
    ...mapState({
      vuex_selected_category: (state) => state.product_wizard.selected_category,
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,
      vuex_selected_search_item: (state) => state.product_wizard.selected_search_item,
      vuex_name: (state) => state.product_wizard.name,
      vuex_search: (state) => state.product_wizard.search,
      vuex_type: (state) => state.product_wizard.wizard_type,
    }),

    /*
     * WIZARD STORE INTEGRATION: Dynamic data sources
     *
     * These computed properties automatically use data from the new
     * Pinia productWizardStore when available (for wizard continuation),
     * falling back to VueX for backward compatibility.
     *
     * USAGE PATTERN:
     * 1. Check Pinia wizard store first (productWizardStore.getStepData)
     * 2. Fall back to VueX store (vuex_selected_*)
     * 3. Provide sensible defaults
     *
     * This enables both fresh component usage (VueX) and wizard continuation (Pinia).
     */
    selected_category() {
      const categoryData = this.productWizardStore.getStepData("categorySearch");

      // Debug what's actually in the categorySearch step
      if (categoryData) {
        console.log("🔍 categorySearch step data:", categoryData, Object.keys(categoryData));
      }

      const result =
        categoryData?.selected_category ||
        categoryData?.selectedCategory ||
        categoryData?.category ||
        this.vuex_selected_category ||
        {};

      return result;
    },

    selected_boops() {
      const boopsData = this.productWizardStore.getStepData("editBoops");
      return boopsData?.selectedBoops || this.vuex_selected_boops || [];
    },

    selected_search_item() {
      const searchData = this.productWizardStore.getStepData("search");
      return searchData?.selectedItem || this.vuex_selected_search_item || {};
    },

    name() {
      const searchData = this.productWizardStore.getStepData("search");
      return searchData?.name || this.vuex_name || "";
    },

    search() {
      const searchData = this.productWizardStore.getStepData("search");
      return searchData?.query || this.vuex_search || "";
    },

    type() {
      return this.productWizardStore.wizardType || this.vuex_type || "";
    },
  },
  mounted() {
    // Sync Pinia data to VueX store (critical for wizard continuation after reload)
    this.syncWizardStoreToVueX();

    // Load existing boops data if available or initialize empty state
    this.initializeEditBoopsStep();

    // Initialize divided state AFTER checking wizard store (only if not already set by initializeEditBoopsStep)
    const existingDivided = this.productWizardStore.getStepData("editBoops")?.divided;
    if (existingDivided === undefined) {
      this.localDivided = this.selected_category?.divided || false;
    }

    // Validate step
    this.validateStep();

    console.log("PrintProductEditBoops mounted:", {
      category: this.selected_category,
      boops: this.selected_boops,
      boopsLength: this.selected_boops?.length,
      divided: this.localDivided,
    });

    // Debug: Check if we have category but no boops
    if (
      this.selected_category?.slug &&
      (!this.selected_boops || this.selected_boops.length === 0)
    ) {
      console.warn("Category found but no boops data - may need to load from API");
    }
  },
  methods: {
    // Legacy VueX mappings (kept for backward compatibility)
    ...mapMutations({
      vuex_set_name: "product_wizard/set_name",
      vuex_set_display_name: "product_wizard/set_display_name",
      vuex_set_selected_category: "product_wizard/set_selected_category",
      vuex_set_selected_boops: "product_wizard/set_selected_boops",
    }),

    /*
     * WIZARD STORE INTEGRATION: Initialize EditBoops step
     *
     * Sets up the editBoops step in the wizard store with current
     * category data and any existing boops configuration.
     *
     * FLOW:
     * 1. Check if editBoops step exists in wizard store
     * 2. If empty/missing: Initialize with empty data structure
     * 3. If exists: Load divided state from existing data
     *
     * This handles both fresh wizard starts and continuation scenarios.
     */
    initializeEditBoopsStep() {
      // Ensure editBoops step is initialized in wizard store
      const existingEditBoops = this.productWizardStore.getStepData("editBoops");

      if (!existingEditBoops || Object.keys(existingEditBoops).length === 0) {
        // Initialize with empty boops if no existing data
        this.productWizardStore.updateStepData("editBoops", {
          selectedBoops: [],
          divided: this.localDivided,
          initialized: true,
          lastUpdated: Date.now(),
        });
        console.log("Initialized empty editBoops step");
      } else {
        // Update divided state from existing data
        if (existingEditBoops.divided !== undefined) {
          this.localDivided = existingEditBoops.divided;
          console.log("🔄 Restored divided state from wizard store:", {
            existingDivided: existingEditBoops.divided,
            localDivided: this.localDivided,
          });
        }
        console.log("Found existing editBoops data:", existingEditBoops);
      }
    },

    /*
     * WIZARD CONTINUATION: Sync Pinia data to VueX after page reload
     *
     * After page reload, the Pinia wizard store loads data from localStorage,
     * but the VueX store is empty. This method syncs the data to ensure
     * both stores have consistent state for API calls and legacy compatibility.
     *
     * CRITICAL FOR: Page reload scenarios where wizard continuation is needed
     */
    syncWizardStoreToVueX() {
      // Only sync if we have data in Pinia but not in VueX
      const hasWizardData =
        this.productWizardStore.stepData &&
        Object.keys(this.productWizardStore.stepData).length > 0;

      if (hasWizardData) {
        // Sync category data
        if (this.selected_category && Object.keys(this.selected_category).length > 0) {
          this.vuex_set_selected_category(this.selected_category);
        }

        // Sync boops data
        if (this.selected_boops && this.selected_boops.length > 0) {
          this.vuex_set_selected_boops(this.selected_boops);
        }

        // Sync search data
        if (this.name) {
          this.vuex_set_name(this.name);
        }
        if (this.display_name) {
          this.vuex_set_display_name(this.display_name);
        }
      }
    },

    /*
     * DUAL STORE UPDATE PATTERN
     *
     * CRITICAL: When updating data, ALWAYS update both stores for consistency.
     *
     * ARCHITECTURE EXPLANATION:
     * - Legacy VueX store: Maintains backward compatibility for existing flows
     * - New Pinia store: Enables wizard continuation via localStorage persistence
     *
     * UPDATE PATTERN:
     * 1. Update VueX store (this.vuex_set_*) - Legacy compatibility
     * 2. Update Pinia store (productWizardStore.updateStepData) - New features
     * 3. Set isDirty flag to trigger auto-save to localStorage
     *
     * This ensures data consistency across both old and new wizard systems.
     */
    set_name(name) {
      this.vuex_set_name(name); // Legacy store
      const currentSearchData = this.productWizardStore.getStepData("search") || {};
      this.productWizardStore.updateStepData("search", {
        // New store + localStorage
        ...currentSearchData,
        name: name,
      });
    },
    set_display_name(displayname) {
      this.vuex_set_display_name(name); // Legacy store
      const currentSearchData = this.productWizardStore.getStepData("search") || {};
      this.productWizardStore.updateStepData("search", {
        // New store + localStorage
        ...currentSearchData,
        display_name: displayname,
      });
    },

    set_selected_category(category) {
      this.vuex_set_selected_category(category); // Legacy store
      this.productWizardStore.updateStepData("categorySearch", {
        // New store + localStorage - use consistent property name
        selected_category: category,
        selectedCategory: category, // Keep both for compatibility
        category: category,
      });
    },

    set_selected_boops(boops) {
      this.vuex_set_selected_boops(boops); // Legacy store
      this.productWizardStore.updateStepData("editBoops", {
        // New store + localStorage
        selectedBoops: boops,
        divided: this.localDivided,
        lastUpdated: Date.now(),
      });
      this.productWizardStore.isDirty = true; // Triggers auto-save
    },

    /*
     * CHILD COMPONENT INTEGRATION: Handle boops updates
     *
     * Called when the EditBoops child component emits @boops-updated.
     * This maintains the reactive data flow between child and parent components.
     *
     * FLOW:
     * 1. Receive updated boops array from EditBoops component
     * 2. Update both stores via set_selected_boops (dual store pattern)
     * 3. Auto-save to localStorage if wizard has been created
     * 4. Log update for debugging
     *
     * This ensures wizard state stays synchronized with user interactions.
     */
    onBoopsUpdated(updatedBoops) {
      this.set_selected_boops(updatedBoops);

      // Auto-save wizard state
      if (this.productWizardStore.categoryCreated) {
        this.productWizardStore.autoSave();
      }

      // Re-validate step after boops are updated
      this.validateStep();

      console.log(`Updated ${updatedBoops.length} boops in wizard store`);
    },

    /**
     * Refresh boops data from API after successful save
     * This ensures the component has the latest data including any server-side changes
     */
    async refreshBoopsData(categoryIdentifier) {
      console.log("🔄 Refreshing boops data from API...", {
        categoryIdentifier,
        apiUrl: `categories/${categoryIdentifier}/boops`,
      });

      try {
        const response = await this.api.get(`categories/${categoryIdentifier}/boops`);
        const freshBoops = response.data?.data || response.data || [];

        console.log("✅ Fresh boops data received:", {
          count: freshBoops.length,
          firstBoop: freshBoops[0],
          responseStructure: {
            hasData: !!response.data?.data,
            hasDirectData: !!response.data,
            responseKeys: Object.keys(response.data || {}),
          },
        });

        console.log("🔄 Before update - current boops:", {
          vuexCount: this.vuex_selected_boops?.length || 0,
          wizardCount: this.productWizardStore.getStepData("editBoops")?.selectedBoops?.length || 0,
        });

        // Update both VueX store and wizard store with fresh data
        this.set_selected_boops(freshBoops);
        this.productWizardStore.updateStepData("editBoops", {
          selectedBoops: freshBoops,
          divided: this.localDivided,
          lastUpdated: Date.now(),
          refreshed: true,
        });

        console.log("🔄 After update - updated boops:", {
          vuexCount: this.vuex_selected_boops?.length || 0,
          wizardCount: this.productWizardStore.getStepData("editBoops")?.selectedBoops?.length || 0,
          wizardData: this.productWizardStore.getStepData("editBoops"),
        });

        console.log("✅ Boops data refreshed successfully");
        return freshBoops;
      } catch (error) {
        console.error("❌ Failed to refresh boops data:", error);
        throw error;
      }
    },

    updateBoops() {
      console.log("🔄 updateBoops() started");
      this.loading = true;

      // Get category from multiple sources
      const computedCategory = this.selected_category;
      const vuexCategory = this.vuex_selected_category;

      // Check for category properties directly instead of Object.keys() (Proxy objects issue)
      const computedHasData =
        computedCategory && (computedCategory.slug || computedCategory.id || computedCategory.name);
      const vuexHasData =
        vuexCategory && (vuexCategory.slug || vuexCategory.id || vuexCategory.name);

      let categoryToUse = computedHasData ? computedCategory : vuexHasData ? vuexCategory : null;

      // If we still don't have category data, let's check the wizard stepData directly
      if (!categoryToUse) {
        const wizardStepData = this.productWizardStore.stepData;
        const categorySearchStep = wizardStepData?.categorySearch;

        console.error("❌ CRITICAL: We have boops but no category data. Investigating...", {
          computedCategory,
          vuexCategory,
          boopsCount: this.selected_boops?.length,
          categorySearchStep: categorySearchStep,
          categorySearchKeys: categorySearchStep
            ? Object.keys(categorySearchStep)
            : "no categorySearch step",
          allStepData: wizardStepData,
        });

        // Try to extract category from the wizard step data directly
        if (categorySearchStep && Object.keys(categorySearchStep).length > 0) {
          console.log("🔄 Found categorySearch step, trying to extract category data...");
          const stepKeys = Object.keys(categorySearchStep);
          console.log("📋 categorySearch step contents:", {
            keys: stepKeys,
            selectedCategory: categorySearchStep.selectedCategory,
            category: categorySearchStep.category,
            fullStep: categorySearchStep,
          });

          // Log each property to see what we actually have
          stepKeys.forEach((key) => {
            console.log(`🔑 ${key}:`, categorySearchStep[key]);
          });

          // Use the correct property name - it's 'selected_category', not 'selectedCategory'
          categoryToUse =
            categorySearchStep.selected_category ||
            categorySearchStep.selectedCategory ||
            categorySearchStep.category ||
            categorySearchStep;

          console.log("🎯 Extracted categoryToUse:", {
            categoryToUse,
            hasSlug: !!categoryToUse?.slug,
            hasId: !!categoryToUse?.id,
            hasName: !!categoryToUse?.name,
            categoryId: categoryToUse?.id,
            categoryName: categoryToUse?.name,
            keys: categoryToUse ? Object.keys(categoryToUse) : "none",
          });
        }

        // Final check - if we still don't have category data, abort
        if (!categoryToUse || (!categoryToUse.slug && !categoryToUse.id && !categoryToUse.name)) {
          console.error(
            "💀 FINAL: Cannot proceed without category data - this would break the API",
          );
          this.loading = false;
          this.validateStep();
          return;
        } else {
          console.log("✅ Recovered category data from wizard step!");
        }
      }

      console.log("✅ Category validation passed for API call");

      // set name
      let name = "";

      // TODO: name here is undefined? What is this?
      // if custom name = not altered
      if (
        Object.keys(this.selected_search_item).length > 0 &&
        this.selected_search_item.name === this.custom_name
      ) {
        name = this.selected_search_item.name;
        // if custom name is changed
      } else if (
        Object.keys(this.selected_search_item).length > 0 &&
        this.selected_search_item.name !== this.custom_name
      ) {
        name = this.custom_name;
        // if no result add as new
      } else {
        this.set_name("");
        name = this.search;
      }

      // Use slug, id, or name as fallback for API endpoint
      const categoryIdentifier = categoryToUse.slug || categoryToUse.id || categoryToUse.name;

      const apiPayload = {
        id: categoryToUse.id,
        name: categoryToUse.name,
        slug: categoryToUse.slug,
        boops: this.selected_boops,
        divided: this.localDivided,
      };

      console.log(
        "📡 Making API call:",
        `categories/${categoryIdentifier}/boops`,
        `(${this.selected_boops?.length || 0} boops)`,
      );

      this.api
        .put(`categories/${categoryIdentifier}/boops`, apiPayload)
        .then((response) => {
          this.handleSuccess(response);

          // Mark step as completed and save wizard state (with refreshed flag for next step)
          const currentStep = this.productWizardStore.currentStep;
          this.productWizardStore.completeStep(currentStep);

          // Update wizard store to indicate data should be refreshed
          this.productWizardStore.updateStepData("editBoops", {
            selectedBoops: this.selected_boops,
            divided: this.localDivided,
            lastUpdated: Date.now(),
            refreshed: true, // Mark as refreshed so next step knows data is fresh
          });
          this.productWizardStore.updateStepData("calcReferences", {
            boops: this.selected_boops,
          });

          this.productWizardStore.autoSave();

          console.log("EditBoops step completed and saved:", {
            stepNumber: currentStep,
            boops: this.selected_boops.length,
            divided: this.localDivided,
          });

          this.loading = false;

          // Navigate to next step in wizard flow
          this.$emit("step-completed", currentStep);
          this.productWizardStore.goToNextStep();
        })
        .catch((error) => {
          this.loading = false;

          // Re-validate step after error to restore Next button state
          this.validateStep();

          this.handleError(error);

          console.log("EditBoops API error:", error);
        });
    },

    /*
     * UI STATE MANAGEMENT: Handle divided toggle changes
     *
     * The "divided" toggle allows products to have separate calculation groups
     * (e.g., book cover vs. inner pages). This handler ensures the state
     * is properly synchronized across all data stores.
     *
     * FLOW:
     * 1. Update local component state (this.localDivided)
     * 2. Persist to wizard store with current boops data
     * 3. Emit to parent component for any additional handling
     *
     * This maintains consistency between UI state and persisted wizard data.
     */
    onDividedChange(value) {
      this.localDivided = value;

      // Update wizard store with new divided state
      this.productWizardStore.updateStepData("editBoops", {
        selectedBoops: this.selected_boops,
        divided: value,
        lastUpdated: Date.now(),
      });

      console.log("🔄 Divided state saved to wizard store:", {
        newValue: value,
        localDivided: this.localDivided,
        savedData: this.productWizardStore.getStepData("editBoops"),
      });

      // Re-validate step after divided state changes
      this.validateStep();

      this.$emit("dividedSelected", value);
    },

    validateStep() {
      // Simplified validation: if we have boops, the step is valid
      // The boops can only exist if a category was selected, so boops = valid step
      const hasBoops = !!(this.selected_boops && this.selected_boops.length > 0);
      const isLoading = !!this.loading;
      const isValid = hasBoops && !isLoading;

      // This emit controls the Next button state in wizard footer navigation
      this.$emit("step-validated", {
        isValid: isValid,
        canProceed: isValid,
        valid: isValid,
        loading: isLoading,
      });

      console.log("EditBoops validation (simplified):", {
        hasBoops,
        boopsCount: this.selected_boops?.length || 0,
        loading: isLoading,
        isValid,
      });
    },

    // Called by wizard navigation when Next button is clicked
    async goNext() {
      console.log("🚀 PrintProductEditBoops - goNext called");

      // Prevent multiple simultaneous calls
      if (this.loading) {
        console.log("Already processing, ignoring duplicate goNext call");
        return;
      }

      // Disable Next button during processing
      this.validateStep();

      console.log("🔄 About to call updateBoops()");
      this.updateBoops(); // Save boops via API, then proceed to next step
    },
  },
};
</script>
