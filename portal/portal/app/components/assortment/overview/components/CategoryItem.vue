<template>
  <li class="relative">
    <button
      class="flex w-full items-center justify-between px-2 py-1 text-left"
      :class="{
        'transition-colors duration-75 hover:bg-gray-200 dark:hover:bg-gray-900': maySelectCategory,
        'cursor-default': !maySelectCategory,
        'bg-theme-50 text-theme-500 hover:bg-theme-50 dark:bg-theme-900 dark:text-theme-200':
          isActive,
        'animate-pulse text-gray-500': hasWizard && !isActive,
      }"
      :disabled="!maySelectCategory"
      @click="$emit('select-category', category)"
    >
      <!-- <img
        v-if="suppliers?.includes(category.tenant_id)"
        :src="`/img/suppliers/images/logos/${getSupplier(category.tenant_id)
          .replace(/\s+/g, '-')
          .toLowerCase()}.jpg`"
      /> -->

      <div
        v-tooltip="
          category.display_name && $display_name(category.display_name).length > 20
            ? `${$display_name(category.display_name)} - ${category.name}`
            : category.name
        "
        class="flex items-center truncate"
      >
        <CategoryIcon :category="category" />

        <span class="w-full truncate" role="presentation">
          {{ category.display_name ? $display_name(category.display_name) : category.name }}
        </span>

        <!-- Wizard indicator icon -->
        <span
          v-if="hasWizard"
          v-tooltip="$t('This category has an unfinished wizard - click to continue')"
          class="relative ml-2 flex-shrink-0"
          @click.self="$emit('continue-wizard', category)"
        >
          <font-awesome-icon :icon="['fal', 'arrow-progress']" class="text-blue-500" size="sm" />
        </span>
      </div>

      <CategoryActions
        :category="category"
        :assortment-flag="assortmentFlag"
        :is-active="isActive"
        :selecting-mode="selectingMode"
        :is-loading="isLoading"
        :calc-method="calcMethod"
        :menu-items="categoryMenuItems"
        :incomplete-calc-refs="incompleteCalcRefs"
        :active-category="activeCategory"
        :necessary-calc-refs="necessaryCalcRefs"
        :has-active-wizard="hasActiveWizard"
        @item-clicked="$emit('menu-item-clicked', $event)"
        @edit-item="$emit('edit-item', category)"
      />
    </button>
  </li>
</template>

<script>
/**
 * CategoryItem Component (Legacy + Wizard Visual Indicators)
 * =========================================================
 *
 * OVERVIEW:
 * Individual category row component that displays category information with
 * legacy functionality enhanced by new wizard flow visual indicators and
 * dynamic menu system.
 *
 * LEGACY FUNCTIONALITY:
 * - Category display with icon, name, and actions
 * - Selection states and hover effects
 * - Permission-based interaction controls
 * - Loading states and tooltips
 *
 * NEW WIZARD FLOW FEATURES:
 *
 * 1. VISUAL INDICATORS:
 *    - Magic wand icon (🪄) with animated pulse effect
 *    - Orange notification dot with pulse animation
 *    - Blue left border (4px) when wizard is active
 *    - Light blue background tint for wizard categories
 *    - Enhanced hover states for wizard categories
 *
 * 2. DYNAMIC MENU SYSTEM:
 *    - categoryMenuItems computed property creates category-specific menus
 *    - Continue/Cancel Wizard actions shown only for wizard categories
 *    - Menu items dynamically updated based on wizard state
 *
 * 3. WIZARD STATE INTEGRATION:
 *    - hasWizard computed property for reactive wizard detection
 *    - Receives hasActiveWizard function from parent (Categories.vue)
 *    - Real-time updates when wizard state changes
 *
 * VISUAL STATE LOGIC:
 * - Normal category: Standard styling, basic menu items
 * - Active category: Theme colors, enhanced menu visibility
 * - Wizard category: Blue border + background, magic icon, wizard menu items
 * - Active + Wizard: Combines both visual treatments
 *
 * CSS CLASSES (Conditional):
 * - Base: flex w-full items-center justify-between px-2 py-1 text-left
 * - Interactive: transition-colors duration-75 hover:bg-gray-200
 * - Active: bg-theme-50 text-theme-500 (theme colors)
 * - Wizard: border-l-4 border-blue-400 bg-blue-50 hover:bg-blue-100
 * - Disabled: cursor-default (when no select permission)
 *
 * PROPS EVOLUTION:
 * Legacy Props: category, assortmentFlag, activeCategory, menuItems, etc.
 * New Props: activeWizardStates, hasActiveWizard (wizard integration)
 *
 * COMPUTED PROPERTIES:
 * - isActive: Determines if this category is currently selected (legacy)
 * - maySelectCategory: Permission check for category selection (legacy)
 * - hasWizard: Checks if this category has active wizard state (new)
 * - categoryMenuItems: Creates dynamic menu with wizard actions (new)
 *
 * WIZARD INDICATOR TEMPLATE:
 * ```vue
 * <span v-if="hasWizard" class="ml-2 flex-shrink-0 relative">
 *   <font-awesome-icon :icon="['fad', 'magic']" class="text-blue-500 animate-pulse" />
 *   <span class="absolute -top-1 -right-1 w-2 h-2 bg-orange-400 rounded-full animate-pulse"></span>
 * </span>
 * ```
 *
 * @see CategoryActions.vue - Handles menu rendering and interactions
 * @see Categories.vue - Parent with wizard state management methods
 * @see CategoryList.vue - Container passing wizard props down
 */

import CategoryIcon from "./CategoryIcon.vue";
import CategoryActions from "./CategoryActions.vue";

export default {
  name: "CategoryItem",
  components: {
    CategoryIcon,
    CategoryActions,
  },
  props: {
    category: {
      type: Object,
      required: true,
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
    isLoading: {
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
    calcMethod: {
      type: Object,
      required: true,
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
  emits: ["select-category", "menu-item-clicked", "edit-item"],
  setup() {
    const { hasPermissionGroup, permissions } = usePermissions();

    return {
      hasPermissionGroup,
      permissions,
    };
  },
  computed: {
    isActive() {
      return this.assortmentFlag === "print_product"
        ? this.activeCategory[0] === this.category.id
        : this.activeCustomCategory.id === this.category.id;
    },
    maySelectCategory() {
      return this.hasPermissionGroup(this.permissions["print-assortments"].groups.categorySelect);
    },
    hasWizard() {
      return this.hasActiveWizard(this.category);
    },
    categoryMenuItems() {
      // Create a copy of the menu items for this specific category
      const menuItems = JSON.parse(JSON.stringify(this.menuItems));

      if (menuItems.length > 0 && menuItems[0].items) {
        // Check if this category has an active wizard
        const hasWizard = this.hasWizard;

        // Find wizard menu items and set their visibility
        menuItems[0].items.forEach((item) => {
          if (item.action === "continue_wizard" || item.action === "cancel_wizard") {
            item.show = hasWizard;
          }
        });
      }

      return menuItems;
    },
  },
  methods: {
    getSupplier(tenantId) {
      // Implementation needed
      return "";
    },
  },
};
</script>
