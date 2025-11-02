<template>
  <div class="h-full overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Product Variants Summary") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Review your product configuration before generating all variants") }}
      </p>
    </div>

    <!-- Main Summary Card -->
    <div
      class="mb-8 overflow-hidden rounded-lg bg-gradient-to-r from-theme-50 to-theme-100 dark:from-theme-800 dark:to-theme-700"
    >
      <div class="p-5">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
              {{ totalVariationsCount }}
            </h3>
            <p class="text-lg text-gray-600 dark:text-gray-300">
              {{ $t("Product variations ready to create") }}
            </p>
          </div>
          <div class="rounded-full bg-blue-100 p-4 dark:bg-blue-900">
            <font-awesome-icon
              :icon="['fad', 'cube']"
              class="h-12 w-12 text-blue-600 dark:text-blue-400"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Configuration Details Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <!-- Category Information -->
      <div class="rounded-lg border-2 bg-white p-4 dark:border-gray-800 dark:bg-gray-700">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
            <font-awesome-icon
              :icon="['fal', 'tag']"
              class="h-6 w-6 text-green-600 dark:text-green-400"
            />
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ $t("Base Category") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{
                $display_name(activeSelectedCategory.display_name) || activeSelectedCategory.name
              }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
              {{ $t("Linked Category ID") }}: {{ activeSelectedCategory.id }}
            </p>
          </div>
        </div>
      </div>

      <!-- Boxes & Options Count -->
      <div class="rounded-lg border-2 bg-white p-4 dark:border-gray-800 dark:bg-gray-700">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
            <font-awesome-icon
              :icon="['fal', 'boxes']"
              class="h-6 w-6 text-purple-600 dark:text-purple-400"
            />
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ boxesCount }} {{ $t("Option Groups") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ totalOptionsCount }} {{ $t("total options") }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
              {{ divided ? $t("Grouped by calculation dividers") : $t("Flat structure") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Exclusions Summary -->
      <div class="rounded-lg border-2 bg-white p-4 dark:border-gray-800 dark:bg-gray-700">
        <div class="flex items-center">
          <div class="rounded-full bg-red-100 p-3 dark:bg-red-900">
            <font-awesome-icon
              :icon="['fal', 'link-slash']"
              class="h-6 w-6 text-red-600 dark:text-red-400"
            />
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ excludesCount }} {{ $t("Exclusion Rules") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ singleExcludesCount }} {{ $t("single") }}, {{ multipleExcludesCount }}
              {{ $t("multiple") }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
              {{ $t("Invalid combinations prevented") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Calculation References Summary -->
      <div class="rounded-lg border-2 bg-white p-4 dark:border-gray-800 dark:bg-gray-700">
        <div class="flex items-center">
          <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
            <font-awesome-icon
              :icon="['fal', 'link']"
              class="h-6 w-6 text-orange-600 dark:text-orange-400"
            />
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ calcRefsConfigured }} {{ $t("Calc References") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ boxesWithCalcRef }} {{ $t("boxes") }}, {{ optionsWithOverride }} {{ $t("overrides") }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
              {{ $t("Pricing calculation configured") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Margins Summary -->
      <div class="rounded-lg border-2 bg-white p-4 dark:border-gray-800 dark:bg-gray-700">
        <div class="flex items-center">
          <div class="rounded-full bg-indigo-100 p-3 dark:bg-indigo-900">
            <font-awesome-icon
              :icon="['fal', 'percent']"
              class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
            />
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ marginsConfigured }} {{ $t("Margin Slots") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ marginsMode ? $t(marginsMode) : $t("Not configured") }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
              {{ $t("Profit margins defined") }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Breakdown -->
    <div class="mt-8">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">
        {{ $t("Configuration Breakdown") }}
      </h3>
      <!-- Option Groups Details -->
      <div class="mb-6">
        <h4 class="text-md mb-4 font-medium text-gray-900 dark:text-white">
          {{ $t("Option Groups") }}
        </h4>
        <div class="space-y-3">
          <div
            v-for="(box, index) in activeSelectedBoops"
            :key="box.id"
            class="flex items-center justify-between rounded border p-3 dark:border-gray-800"
          >
            <div class="flex items-center">
              <div class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700">
                {{ index + 1 }}
              </div>
              <div class="ml-3">
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ $display_name(box.display_name) }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ box.ops?.length || 0 }} {{ $t("options") }}
                  <span v-if="divided && box.divider" class="ml-2 text-xs">
                    ({{ box.divider }})
                  </span>
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ getBoxExcludesCount(box) }} {{ $t("excludes") }} (ID: {{ box.id }})
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Calculation References Details -->
      <div v-if="calcRefsConfigured > 0" class="mb-6">
        <h4 class="text-md mb-4 font-medium text-gray-900 dark:text-white">
          {{ $t("Calculation References") }}
        </h4>
        <div class="space-y-2">
          <div
            v-for="box in activeSelectedBoops.filter((b) => b.calc_ref)"
            :key="'calc-' + box.id"
            class="flex items-center justify-between rounded border p-2 dark:border-gray-800"
          >
            <div class="flex items-center">
              <font-awesome-icon :icon="['fal', 'cube']" class="mr-2 text-orange-500" />
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $display_name(box.display_name) }}
              </span>
            </div>
            <span class="text-xs text-gray-600 dark:text-gray-400">
              {{ $t(box.calc_ref) }}
            </span>
          </div>
          <div v-if="optionsWithOverride > 0" class="mt-2 rounded bg-orange-50 p-2 dark:bg-orange-900/20">
            <p class="text-xs text-orange-800 dark:text-orange-200">
              <font-awesome-icon :icon="['fal', 'info-circle']" class="mr-1" />
              {{ optionsWithOverride }} {{ $t("options have custom calc_ref overrides or additional properties") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Margins Details -->
      <div v-if="marginsConfigured > 0" class="mb-6">
        <h4 class="text-md mb-4 font-medium text-gray-900 dark:text-white">
          {{ $t("Profit Margins") }}
        </h4>
        <div class="space-y-2">
          <div class="rounded bg-indigo-50 p-3 dark:bg-indigo-900/20">
            <p class="text-sm text-indigo-800 dark:text-indigo-200">
              <font-awesome-icon :icon="['fal', 'percent']" class="mr-2" />
              {{ marginsConfigured }} {{ $t("margin slots configured") }} -
              {{ $t("Mode") }}: <strong>{{ $t(marginsMode) }}</strong>
            </p>
          </div>
        </div>
      </div>

      <!-- Calculation Method -->
      <div class="mb-6">
        <h4 class="text-md mb-2 font-medium text-gray-900 dark:text-white">
          {{ $t("Pricing Method") }}
        </h4>
        <div class="rounded bg-blue-50 p-3 dark:bg-blue-900/20">
          <p class="text-sm text-blue-800 dark:text-blue-200">
            <font-awesome-icon :icon="['fal', 'calculator']" class="mr-2" />
            {{ $t("Full Calculation Method") }} -
            {{ $t("All combinations will be calculated individually") }}
          </p>
        </div>
      </div>

      <!-- Generation Impact -->
      <div class="rounded bg-yellow-50 p-4 dark:bg-yellow-900/20">
        <div class="flex">
          <font-awesome-icon
            :icon="['fal', 'info-circle']"
            class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
          />
          <div class="ml-3">
            <h5 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
              {{ $t("Generation Impact") }}
            </h5>
            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
              {{ $t("This will create") }} <strong>{{ totalVariationsCount }}</strong>
              {{
                $t(
                  "individual product variants in your catalog. Each variant will be available for ordering and pricing.",
                )
              }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * PrintProductManifest Component
 *
 * Final step in the product wizard that displays a comprehensive summary
 * of all configured product variants before generation.
 *
 * Features:
 * - Variant count calculation based on box/option combinations
 * - Configuration breakdown with exclude rules impact
 * - Category information and pricing method display
 * - Real-time statistics for boxes, options, and exclusions
 * - Final generation action with progress feedback
 *
 * Data Sources:
 * - VueX product_wizard state for selected_category, selected_boops
 * - AddProductSearch state for exclude rules (single/multiple)
 * - Calculation logic for variant combinations
 *
 * @component PrintProductManifest
 */

import { mapState, mapMutations } from "vuex";
import { useProductWizardStore } from "../../../../stores/productWizard.js";

export default {
  name: "PrintProductManifest",
  emits: ["previous-step"],
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
    return { capitalizeFirstLetter, api, handleError, handleSuccess };
  },
  data() {
    return {
      loading: false,
      manageExcludes: false,
    };
  },

  mounted() {
    // Emit that this step is valid since it's just a summary
    this.$emit("step-validated", {
      isValid: true,
      canProceed: true,
      stepNumber: 7,
    });
  },
  computed: {
    ...mapState({
      selected_category: (state) => state.product_wizard.selected_category,
      selected_boops: (state) => state.product_wizard.selected_boops,
      generated_manifest: (state) => state.product_wizard.generated_manifest,
    }),

    /**
     * Get selected category from ProductWizardStore or VueX fallback
     */
    activeSelectedCategory() {
      try {
        const productWizardStore = useProductWizardStore();
        return (
          productWizardStore.getStepData("categorySearch")?.selectedCategory ||
          this.selected_category ||
          {}
        );
      } catch (error) {
        return this.selected_category || {};
      }
    },

    /**
     * Get selected boops from ProductWizardStore or VueX fallback
     */
    activeSelectedBoops() {
      try {
        const productWizardStore = useProductWizardStore();
        return (
          productWizardStore.getStepData("editBoops")?.selectedBoops || this.selected_boops || []
        );
      } catch (error) {
        return this.selected_boops || [];
      }
    },

    /**
     * Get exclude data from ProductWizardStore
     */
    activeExcludeData() {
      // Excludes are stored in each option (op) inside each box (boop) as op.excludes
      const singleExcludes = {};
      const multipleExcludes = {};

      if (!this.activeSelectedBoops?.length) {
        return { singleExcludes, multipleExcludes };
      }

      this.activeSelectedBoops.forEach((box) => {
        if (!box.ops?.length) return;

        box.ops.forEach((op) => {
          if (Array.isArray(op.excludes) && op.excludes.length) {
            // If op.excludes has more than one string, it's a multiple exclude
            if (op.excludes.length > 1) {
              if (!multipleExcludes[box.id]) multipleExcludes[box.id] = [];
              multipleExcludes[box.id].push(op.excludes);
            } else {
              // Single exclude (only one string in array)
              if (!singleExcludes[box.id]) singleExcludes[box.id] = [];
              singleExcludes[box.id].push(op.excludes[0]);
            }
          }
        });
      });

      // No multipleExcludes logic defined in ops, so keep empty for now
      return { singleExcludes, multipleExcludes };
    },

    /**
     * Calculate total number of product variations
     * Based on all combinations of options across all boxes, minus excludes impact
     * @returns {number} Total possible product variants
     */
    totalVariationsCount() {
      if (!this.activeSelectedBoops?.length) return 0;

      // Calculate base combinations (multiply options in each box)
      const baseCombinations = this.activeSelectedBoops.reduce((total, box) => {
        const optionsCount = box.ops?.length || 0;
        return total * Math.max(optionsCount, 1);
      }, 1);

      // For display purposes, show the base combinations
      // In reality, excludes would reduce this number but calculation is complex
      return baseCombinations;
    },

    /**
     * Count total number of option boxes/groups
     * @returns {number} Number of option groups configured
     */
    boxesCount() {
      return this.activeSelectedBoops?.length || 0;
    },

    /**
     * Count total options across all boxes
     * @returns {number} Sum of all options in all boxes
     */
    totalOptionsCount() {
      if (!this.activeSelectedBoops?.length) return 0;

      return this.activeSelectedBoops.reduce((total, box) => {
        return total + (box.ops?.length || 0);
      }, 0);
    },

    /**
     * Count total exclude rules (single + multiple)
     * @returns {number} Total number of exclusion rules
     */
    excludesCount() {
      return this.singleExcludesCount + this.multipleExcludesCount;
    },

    /**
     * Count single exclude rules (one-to-one exclusions)
     * @returns {number} Number of single exclusion rules
     */
    singleExcludesCount() {
      const excludeData = this.activeExcludeData;
      if (!excludeData.singleExcludes) return 0;

      return Object.values(excludeData.singleExcludes).reduce((total, excludes) => {
        return total + (excludes?.length || 0);
      }, 0);
    },

    /**
     * Count multiple exclude rules (group exclusions)
     * @returns {number} Number of multiple exclusion rules
     */
    multipleExcludesCount() {
      const excludeData = this.activeExcludeData;
      if (!excludeData.multipleExcludes) return 0;

      return Object.values(excludeData.multipleExcludes).reduce((total, excludes) => {
        return total + (excludes?.length || 0);
      }, 0);
    },

    /**
     * Get calc references data from ProductWizardStore
     */
    activeCalcRefsData() {
      try {
        const productWizardStore = useProductWizardStore();
        return productWizardStore.getStepData("calcReferences") || {};
      } catch (error) {
        return {};
      }
    },

    /**
     * Count total configured calc references
     * @returns {number} Total calc refs configured
     */
    calcRefsConfigured() {
      return this.boxesWithCalcRef + this.optionsWithOverride;
    },

    /**
     * Count boxes with calc_ref configured
     * @returns {number} Number of boxes with calc_ref
     */
    boxesWithCalcRef() {
      if (!this.activeSelectedBoops?.length) return 0;

      return this.activeSelectedBoops.filter((box) => box.calc_ref && box.calc_ref !== "").length;
    },

    /**
     * Count options with override configured
     * @returns {number} Number of options with calc_ref override
     */
    optionsWithOverride() {
      if (!this.activeSelectedBoops?.length) return 0;

      let count = 0;
      this.activeSelectedBoops.forEach((box) => {
        if (!box.ops?.length) return;

        box.ops.forEach((op) => {
          if (op.additional?.override_calc_ref || op.additional?.calc_ref_type) {
            count++;
          }
        });
      });

      return count;
    },

    /**
     * Get margins data from ProductWizardStore
     */
    activeMarginsData() {
      try {
        const productWizardStore = useProductWizardStore();
        return productWizardStore.getStepData("margins") || {};
      } catch (error) {
        return {};
      }
    },

    /**
     * Count configured margin slots
     * @returns {number} Number of margin slots
     */
    marginsConfigured() {
      return this.activeMarginsData?.margins?.length || 0;
    },

    /**
     * Get margins mode (runs/price)
     * @returns {string} Margins mode
     */
    marginsMode() {
      return this.activeMarginsData?.mode || "";
    },
  },

  methods: {
    ...mapMutations({
      set_search: "product_wizard/set_search",
      set_custom_name: "product_wizard/set_custom_name",
      set_selected_search_item: "product_wizard/set_selected_search_item",
      set_selected_category: "product_wizard/set_selected_category",
      set_selected_boops: "product_wizard/set_selected_boops",
      set_selected_box: "product_wizard/set_selected_box",
      set_selected_option: "product_wizard/set_selected_option",
      set_component: "product_wizard/set_wizard_component",
    }),

    /**
     * Method called by wizard navigation when "Next/Finish" is clicked
     */
    async goNext() {
      // This is the final step - trigger generation
      await this.generateManifest();
    },

    /**
     * Toggle exclude management mode (legacy method for compatibility)
     * @param {boolean} value - New manage excludes state
     */
    changeManageExcludes(value) {
      this.manageExcludes = value;
    },

    /**
     * Get exclude count for a specific box
     * @param {Object} box - Box object to count excludes for
     * @returns {number} Number of excludes for this box
     */
    getBoxExcludesCount(box) {
      if (!box?.id) return 0;

      const excludeData = this.activeExcludeData;
      const singleCount = excludeData.singleExcludes?.[box.id]?.length || 0;
      const multipleCount = excludeData.multipleExcludes?.[box.id]?.length || 0;

      // Debug log to see what's happening
      console.log(`Box ${box.id} (${box.name}):`, {
        singleExcludes: excludeData.singleExcludes,
        multipleExcludes: excludeData.multipleExcludes,
        singleCount,
        multipleCount,
      });

      return singleCount + multipleCount;
    },

    /**
     * Check if using divided mode
     */
    divided() {
      try {
        const productWizardStore = useProductWizardStore();
        return productWizardStore.getStepData("editBoops")?.divided || this.props?.divided || false;
      } catch (error) {
        return this.props?.divided || false;
      }
    },

    /**
     * Generate the final product manifest and create all variants
     * Calls the API to create all product combinations based on configuration
     *
     * @async
     * @returns {Promise<void>}
     * @throws {Error} If generation fails
     */
    generateManifest() {
      this.loading = true;

      // Get active data
      const category = this.activeSelectedCategory;
      const boops = this.activeSelectedBoops;
      const excludeData = this.activeExcludeData;

      // Prepare payload with category and boops data
      const payload = {
        id: category.id,
        name: category.name,
        slug: category.slug,
        boops: boops,
        divided: this.divided,
      };

      this.api
        .post(`categories/${category.slug}/products/combinations/generate`, payload)
        .then((response) => {
          // Clear wizard state on successful generation
          this.set_search("");
          this.set_custom_name("");
          this.set_selected_search_item({});
          this.set_selected_category({});
          this.set_selected_boops([]);
          this.set_selected_box({});
          this.set_selected_option({});

          // Clean up wizard state from localStorage
          const productWizardStore = useProductWizardStore();
          const route = useRoute();
          const wizardId = route.query.continue || category.id || category.slug;
          productWizardStore.completeWizard(wizardId);
          console.log("Wizard completed and cleaned up for category:", category.name);

          // Show success message
          this.handleSuccess(response);

          // Navigate back to overview after generation
          setTimeout(() => {
            this.set_component("AddProductOverview");
            this.$router.push("/assortment");
          }, 1000);
        })
        .catch((error) => {
          console.error("Product generation failed:", error);
          this.handleError(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>
