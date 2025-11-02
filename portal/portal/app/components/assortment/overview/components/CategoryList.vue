<template>
  <nav>
    <TransitionGroup
      v-if="
        (categories && categories.length > 0) || (customCategories && customCategories.length > 0)
      "
      name="category-list"
      tag="ul"
    >
      <CategoryItem
        v-for="category in displayedCategories"
        :key="category.id"
        :category="category"
        :assortment-flag="assortmentFlag"
        :active-category="activeCategory"
        :active-custom-category="activeCustomCategory"
        :selecting-mode="selectingMode"
        :is-loading="
          loadingBoops &&
          activeCategory[2] ===
            (category.display_name ? $display_name(category.display_name) : category.name)
        "
        :menu-items="menuItems"
        :suppliers="suppliers"
        :necessary-calc-refs="necessaryCalcRefs"
        :incomplete-calc-refs="incompleteCalcRefs"
        :calc-method="calcMethod(category)"
        :active-wizard-states="activeWizardStates"
        :has-active-wizard="hasActiveWizard"
        @select-category="onSelectCategory"
        @menu-item-clicked="(event) => $emit('menu-item-clicked', event, category)"
        @edit-item="onEditItem"
        @continue-wizard="(category) => $emit('continue-wizard', category)"
      />
    </TransitionGroup>

    <section v-else class="w-full p-2 text-center">
      <p class="my-2 italic text-gray-400">{{ $t("No categories") }}</p>
      <nuxt-link
        :to="'/assortment/add-products'"
        class="rounded-full border border-theme-500 px-2 py-1 text-sm font-normal text-theme-500 transition-colors duration-75 hover:text-theme-700"
      >
        <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
        {{ $t("add your first category") }}
      </nuxt-link>
    </section>
  </nav>
</template>

<script>
/**
 * CategoryList Component (Legacy + Wizard Flow Integration)
 * ========================================================
 *
 * OVERVIEW:
 * Container component that renders a list of CategoryItem components with both
 * legacy category functionality and new wizard flow integration.
 *
 * LEGACY FUNCTIONALITY:
 * - Renders filtered categories based on assortment type (print/custom)
 * - Handles category selection events and menu interactions
 * - Displays empty state with "add first category" link
 * - Calculation method determination per category
 *
 * NEW WIZARD FLOW FEATURES:
 * - Passes wizard-related props to CategoryItem components
 * - Enables wizard state detection for visual indicators
 * - Supports wizard menu actions (continue/cancel)
 *
 * PROP FLOW (Wizard-Related):
 * Categories.vue → CategoryList.vue → CategoryItem.vue
 * - activeWizardStates: Array of active wizard states from localStorage
 * - hasActiveWizard: Function to check if category has wizard state
 *
 * KEY PROPS:
 * @prop {Array} activeWizardStates - Active wizard states from productWizardStore
 * @prop {Function} hasActiveWizard - Function to detect wizard for category
 * @prop {Array} menuItems - Menu configuration with wizard actions
 *
 * RENDERING LOGIC:
 * - displayedCategories computed filters categories by search term
 * - Each CategoryItem receives wizard props for individual processing
 * - Empty state encourages new category creation via wizard
 *
 * @see CategoryItem.vue - Individual category with wizard indicators
 * @see Categories.vue - Parent component with wizard state management
 */

import CategoryItem from "./CategoryItem.vue";

export default {
  name: "CategoryList",
  components: {
    CategoryItem,
  },
  props: {
    categories: {
      type: Array,
      default: () => [],
    },
    customCategories: {
      type: Array,
      default: () => [],
    },
    assortmentFlag: {
      type: String,
      required: true,
    },
    activeCategory: {
      type: Array,
      required: true,
    },
    activeCustomCategory: {
      type: Object,
      required: true,
    },
    selectingMode: {
      type: Boolean,
      required: true,
    },
    loadingBoops: {
      type: Boolean,
      default: false,
    },
    menuItems: {
      type: Array,
      required: true,
    },
    suppliers: {
      type: Array,
      default: () => [],
    },
    necessaryCalcRefs: {
      type: Object,
      required: true,
    },
    incompleteCalcRefs: {
      type: Boolean,
      required: true,
    },
    filter: {
      type: String,
      default: "",
    },
    activeWizardStates: {
      type: Array,
      default: () => [],
    },
    hasActiveWizard: {
      type: Function,
      required: true,
    },
  },
  emits: ["select-category", "select-custom-category", "menu-item-clicked", "edit-item"],
  computed: {
    displayedCategories() {
      if (this.assortmentFlag === "print_product") {
        return this.categories.filter((category) =>
          category.name.toLowerCase().includes(this.filter.toLowerCase()),
        );
      } else {
        return this.customCategories.filter((category) =>
          category.name.toLowerCase().includes(this.filter.toLowerCase()),
        );
      }
    },
  },
  methods: {
    onSelectCategory(category) {
      if (this.assortmentFlag === "print_product") {
        this.$emit("select-category", category);
      } else {
        this.$emit("select-custom-category", category);
      }
    },
    onEditItem(category) {
      this.$emit("edit-item", category);
    },
    calcMethod(category) {
      if (category?.price_build?.full_calculation) {
        return {
          type: "full_calculation",
          icon: "calculator",
          tooltip: this.$t("this category uses the FULL CALCULATION for pricing"),
        };
      } else if (category?.price_build?.semi_calculation) {
        return {
          type: "semi_calculation",
          icon: "box-full",
          tooltip: this.$t("this category uses the SEMI CALCULATION for pricing"),
        };
      } else if (category?.price_build?.external_calculation) {
        return {
          type: "external_calculation",
          icon: "cloud",
          tooltip: this.$t("this category is from an external producer"),
        };
      } else {
        return {
          type: "collection",
          icon: "album-collection",
          tooltip: this.$t("this category uses the COLLECTION method for pricing"),
        };
      }
    },
  },
};
</script>
