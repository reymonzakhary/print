<template>
  <div class="h-full w-full overflow-auto">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Manage Option Exclusions") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Configure which options cannot be selected together for this product category") }}
      </p>
    </div>

    <!-- <div class="my-4 text-sm">
      <span
        class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-800"
      >
        <font-awesome-icon :icon="['fal', 'up']" fixed-width />
        Shift
      </span>
      +
      <span
        class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-800"
      >
        <font-awesome-icon :icon="['fal', 'computer-mouse-scrollwheel']" fixed-width />
        scroll
        <font-awesome-icon :icon="['fal', 'sort']" fixed-width />
      </span>
      <span class="text-gray-500 dark:text-gray-300">{{ $t("for horizontal scrolling") }}</span>
    </div> -->

    <!-- <div class="w-full overflow-auto rounded border dark:border-gray-800"> -->
    <ManageExcludes
      :divided="localDivided"
      :selected-boops="selected_boops"
      :selected-box="selected_box"
      :selected-option="selected_option"
      :selected-divider="selected_divider"
    />
    <!-- </div> -->

    <!-- Navigation removed - handled by wizard footer -->

    <!-- confirmation modal -->
    <confirmation-modal v-if="showModal">
      <template #modal-header>
        {{ selected_category.name }}
      </template>
      <template #modal-body>
        <span class="my-4 flex items-center justify-around">
          <font-awesome-icon class="fa-3x text-theme-500" :icon="['fad', 'boxes-stacked']" />
          <font-awesome-icon class="fa-2x text-theme-500" :icon="['fad', 'arrow-right']" />
          <font-awesome-icon class="fa-5x text-theme-500" :icon="['fad', 'store']" />
        </span>
        <b class="capitalize">{{ selected_category.name }}</b>
        {{ $t("from") }} <b>{{ selected_producer.name }}</b> {{ $t("added to your shop") }}!
      </template>

      <template #confirm-button>
        <button
          class="mr-2 rounded-full bg-green-500 px-4 py-1 text-lg text-white transition-colors hover:bg-green-600"
          @click="closeModal()"
        >
          {{ $t("thank you") }}
        </button>
      </template>
      <template #cancel-button>
        <span />
      </template>
    </confirmation-modal>
  </div>
</template>

<script>
/**
 * PrintProductEditBoopsManageExcludes - Step 6 Product Wizard Exclude Management
 *
 * Optional wizard step for configuring option exclusions. Prevents invalid option
 * combinations in product configurator (e.g., A4 ↔ 135gsm paper weight).
 *
 * Features: Single/multiple excludes, divider grouping toggle, visual indicators,
 * dual-store pattern (Pinia + VueX), API persistence, wizard step integration
 *
 * @component PrintProductEditBoopsManageExcludes
 * @since 1.0.0
 */

import { mapState, mapMutations } from "vuex";

export default {
  name: "PrintProductEditBoopsManageExcludes",
  emits: ["step-completed", "step-validated", "next-step", "previous-step"],
  props: {
    divided: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    const productWizardStore = useProductWizardStore();

    return {
      capitalizeFirstLetter,
      api,
      handleError,
      handleSuccess,
      productWizardStore,
    };
  },
  data() {
    return {
      loading: false,
      showModal: false,
      localDivided: false,
      categoryBoops: [], // Full category boops with dividers for excludes
    };
  },
  computed: {
    // Legacy VueX mappings (kept for backward compatibility)
    ...mapState({
      vuex_selected_producer: (state) => state.product_wizard.selected_producer,
      vuex_selected_category: (state) => state.product_wizard.selected_category,
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,
      vuex_selected_box: (state) => state.product_wizard.selected_box,
      vuex_selected_option: (state) => state.product_wizard.selected_option,
      vuex_selected_divider: (state) => state.product_wizard.selected_divider,
      vuex_wizard_type: (state) => state.product_wizard.wizard_type,
    }),

    /**
     * DUAL-STORE DATA ACCESS PATTERN
     *
     * Priority order for data access:
     * 1. Check Pinia wizard store first (productWizardStore.getStepData)
     * 2. Fall back to VueX store (vuex_selected_*)
     * 3. Provide sensible defaults
     *
     * This enables both fresh component usage (VueX) and wizard continuation (Pinia).
     */
    selected_category() {
      const categoryData = this.productWizardStore.getStepData("categorySearch");
      return (
        categoryData?.selected_category ||
        categoryData?.selectedCategory ||
        categoryData?.category ||
        this.vuex_selected_category ||
        {}
      );
    },

    selected_producer() {
      const producerData = this.productWizardStore.getStepData("producerSearch");
      return (
        producerData?.selectedProducer ||
        producerData?.producer ||
        this.vuex_selected_producer ||
        {}
      );
    },

    selected_boops() {
      const boopsData = this.productWizardStore.getStepData("editBoops");
      return boopsData?.selectedBoops || this.vuex_selected_boops || [];
    },

    selected_box() {
      const excludesData = this.productWizardStore.getStepData("manageExcludes");
      return excludesData?.selectedBox || this.vuex_selected_box || {};
    },

    selected_option() {
      const excludesData = this.productWizardStore.getStepData("manageExcludes");
      return excludesData?.selectedOption || this.vuex_selected_option || {};
    },

    selected_divider() {
      const excludesData = this.productWizardStore.getStepData("manageExcludes");
      return excludesData?.selectedDivider || this.vuex_selected_divider || "";
    },

    type() {
      return this.productWizardStore.productDefinition?.type || this.vuex_wizard_type || "print";
    },

    // Validation for step completion
    isStepValid() {
      // ManageExcludes step is always valid - it's optional configuration
      // User can proceed without configuring any excludes
      return !this.loading;
    },
  },
  watch: {
    selected_boops: {
      deep: true,
      handler(newVal) {
        if (newVal) {
          this.validateStep();
        }
      },
    },
    selected_box: {
      deep: true,
      handler(newVal) {
        if (newVal) {
          this.validateStep();
        }
      },
    },
    selected_option: {
      deep: true,
      handler(newVal) {
        if (newVal) {
          this.validateStep();
        }
      },
    },
    loading(newVal) {
      // Re-validate when loading state changes
      this.validateStep();
    },
  },
  async mounted() {
    // Initialize step data
    this.initializeManageExcludesStep();

    // Set up local state - will be updated after loading category data
    this.localDivided = this.selected_category?.divided || this.divided;

    // Check if we have fresh boops data from EditBoops step first
    const editBoopsData = this.productWizardStore.getStepData("editBoops");
    const hasFreshBoopsData =
      editBoopsData?.selectedBoops &&
      editBoopsData?.refreshed &&
      editBoopsData?.lastUpdated &&
      Date.now() - editBoopsData.lastUpdated < 60000; // Less than 1 minute old

    if (hasFreshBoopsData) {
      // Use fresh data from EditBoops step
      this.categoryBoops = editBoopsData.selectedBoops;
    } else {
      // Load full category boops for excludes (these have dividers)
      if (this.selected_category?.slug) {
        try {
          const response = await this.api.get(`/categories/${this.selected_category.slug}`);

          if (response.data.boops && response.data.boops[0]?.boops) {
            this.categoryBoops = response.data.boops[0].boops;
          }
        } catch (error) {
          console.error("Error loading category boops:", error);
        }
      }
    }

    // Process the boops data (whether from wizard store or API)
    if (this.categoryBoops && this.categoryBoops.length > 0) {
      // Determine divided based on actual divider content
      const hasRealDividers = this.categoryBoops.some(
        (boop) => boop.divider && boop.divider.trim() !== "",
      );

      // Respect user's divided choice from EditBoops step if available
      const editBoopsDivided = editBoopsData?.divided;
      if (editBoopsDivided !== undefined) {
        this.localDivided = editBoopsDivided;
      } else {
        this.localDivided = hasRealDividers;
      }

      // Always update selected_boops with category data (includes dividers and complete structure)
      this.set_selected_boops(this.categoryBoops);
    }

    // Validate step
    this.validateStep();
  },
  methods: {
    /**
     * DIVIDER TOGGLE METHOD
     */
    toggleDivider() {
      this.localDivided = !this.localDivided;

      // Save divider setting to wizard store
      this.productWizardStore.updateStepData("manageExcludes", {
        divided: this.localDivided,
        dividerToggleTimestamp: Date.now(), // Track when user manually changed it
      });
    },

    // Legacy VueX mappings (kept for backward compatibility)
    ...mapMutations({
      vuex_set_selected_category: "product_wizard/set_selected_category",
      vuex_set_selected_boops: "product_wizard/set_selected_boops",
      vuex_set_selected_box: "product_wizard/set_selected_box",
      vuex_set_selected_option: "product_wizard/set_selected_option",
      vuex_set_selected_divider: "product_wizard/set_selected_divider",
      vuex_set_generated_manifest: "product_wizard/set_generated_manifest",
      vuex_set_component: "product_wizard/set_wizard_component",
    }),

    /**
     * WIZARD STEP INTEGRATION METHODS
     */

    initializeManageExcludesStep() {
      // Initialize step data if not exists
      const existingData = this.productWizardStore.getStepData("manageExcludes");
      if (!existingData || Object.keys(existingData).length === 0) {
        this.productWizardStore.updateStepData("manageExcludes", {
          selectedBox: {},
          selectedOption: {},
          excludeSettings: {},
          lastUpdated: Date.now(),
        });
      }
    },

    validateStep() {
      const isValid = this.isStepValid;

      this.$emit("step-validated", {
        isValid,
        canProceed: isValid,
        valid: isValid,
        loading: this.loading,
      });
    },

    // Called by wizard when Next button is clicked
    async goNext() {
      if (!this.isStepValid) {
        return;
      }

      try {
        await this.saveExcludeSettings();

        // Mark step as completed and proceed
        const currentStep = this.productWizardStore.currentStep;
        this.$emit("step-completed", currentStep);
        this.productWizardStore.completeStep(currentStep);
        this.productWizardStore.goToNextStep();
      } catch (error) {
        console.error("Error in goNext:", error);
        this.handleError(error);
      }
    },

    /**
     * DATA PERSISTENCE METHODS
     */

    set_selected_category(category) {
      this.vuex_set_selected_category(category); // Legacy store
      this.productWizardStore.updateStepData("categorySearch", {
        selected_category: category,
        selectedCategory: category,
        category: category,
      });
    },

    set_selected_boops(boops) {
      this.vuex_set_selected_boops(boops); // Legacy store
      this.productWizardStore.updateStepData("editBoops", {
        selectedBoops: boops,
        divided: this.localDivided,
        lastUpdated: Date.now(),
      });
    },

    set_selected_box(box) {
      this.vuex_set_selected_box(box); // Legacy store
      this.productWizardStore.updateStepData("manageExcludes", {
        selectedBox: box,
        selectedOption: this.selected_option,
        excludeSettings:
          this.productWizardStore.getStepData("manageExcludes")?.excludeSettings || {},
        lastUpdated: Date.now(),
      });
      this.productWizardStore.isDirty = true;
    },

    set_selected_option(option) {
      this.vuex_set_selected_option(option); // Legacy store
      this.productWizardStore.updateStepData("manageExcludes", {
        selectedBox: this.selected_box,
        selectedOption: option,
        excludeSettings:
          this.productWizardStore.getStepData("manageExcludes")?.excludeSettings || {},
        lastUpdated: Date.now(),
      });
      this.productWizardStore.isDirty = true;
    },

    set_selected_divider(divider) {
      // Legacy VueX mutation (if it exists)
      if (this.vuex_set_selected_divider) {
        this.vuex_set_selected_divider(divider);
      }

      // Update wizard store
      this.productWizardStore.updateStepData("manageExcludes", {
        selectedBox: this.selected_box,
        selectedOption: this.selected_option,
        selectedDivider: divider,
        excludeSettings:
          this.productWizardStore.getStepData("manageExcludes")?.excludeSettings || {},
        lastUpdated: Date.now(),
      });
      this.productWizardStore.isDirty = true;
    },

    /**
     * API METHODS
     */

    async saveExcludeSettings() {
      if (!this.selected_category?.slug) {
        return;
      }

      this.loading = true;

      try {
        const response = await this.api.put(`categories/${this.selected_category.slug}/boops`, {
          id: this.selected_category.id,
          name: this.selected_category.name,
          slug: this.selected_category.slug,
          boops: this.selected_boops,
          divided: this.localDivided,
        });

        this.handleSuccess(response);

        // Save the updated boops data back to wizard store
        this.set_selected_boops(this.selected_boops);

        return response;
      } catch (error) {
        console.error("❌ Error saving exclude settings:", error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    closeModal() {
      this.showModal = false;
      this.$router.push("/assortment");
    },
  },
};
</script>

<style></style>
