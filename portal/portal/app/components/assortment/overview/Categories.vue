<template>
  <section class="h-full w-80 pb-2 sm:w-96">
    <section
      class="mr-1 box-border h-full overflow-y-auto rounded bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
      :class="{
        'border-2 border-theme-300 shadow-xl shadow-gray-300': selectingMode,
      }"
    >
      <!-- Header Component -->
      <CategoryHeader
        :cart-flag="cart_flag"
        :assortment-flag="assortment_flag"
        @set-cart-flag="set_cart_flag"
        @activate-modal="activateModal"
      />

      <!-- Assortment Type Selector -->
      <AssortmentTypeSelector
        :assortment-flag="assortment_flag"
        :compare-flag="compare_flag"
        @switch-assortment-type="switchAssortmentType"
      />

      <!-- Filter -->
      <CategoryFilter v-model="filter" :categories-count="categories.length" />

      <!-- Category List -->
      <CategoryList
        :categories="categories"
        :custom-categories="custom_categories"
        :assortment-flag="assortment_flag"
        :active-category="active_category"
        :active-custom-category="active_custom_category"
        :selecting-mode="selectingMode"
        :loading-boops="$store.state.product.loading_boops"
        :menu-items="menuItems"
        :suppliers="suppliers"
        :necessary-calc-refs="necesaryCalcRefs"
        :incomplete-calc-refs="incompleteCalcRefs"
        :filter="filter"
        :active-wizard-states="activeWizardStates"
        :has-active-wizard="hasActiveWizard"
        @select-category="selectCategory"
        @select-custom-category="selectCustomCategory"
        @menu-item-clicked="menuItemClicked"
        @edit-item="handleEditItem"
        @continue-wizard="continueWizard"
      />

      <pagination class="mt-2" @pagination="get_categories($event.page)" />
    </section>

    <!-- Side Panel -->
    <CategorySidePanel
      :component="component"
      :categories="theCategories"
      :active-category="active_category"
      :active-custom-category="active_custom_category"
      :raw-component="rawComponent"
      @on-category-delete="handleCategoryDelete"
      @on-custom-category-update="handleCustomCategoryUpdate"
      @close-panel="set_component('')"
    />
  </section>
</template>

<script>
/**
 * Categories Component (Legacy + New Wizard Flow Support)
 * ====================================================
 *
 * OVERVIEW:
 * This component manages both the legacy category management system and the new
 * product wizard flow integration. It serves as the main container for category
 * listing, selection, and wizard state management.
 *
 * LEGACY FUNCTIONALITY:
 * - Category listing and filtering (print & custom products)
 * - Category selection and menu operations (edit, delete, export, import)
 * - Permission-based menu item visibility
 * - VueX store integration for category state management
 *
 * NEW WIZARD FLOW FEATURES:
 * - Visual indicators for categories with unfinished wizards
 * - Wizard state persistence and recovery via localStorage
 * - Continue/Cancel wizard menu actions
 * - Real-time wizard state detection and UI updates
 * - Integration with Pinia productWizardStore
 *
 * WIZARD INTEGRATION LOGIC:
 * - hasActiveWizard(): Checks if category has saved wizard state
 * - getActiveWizard(): Retrieves specific wizard data for category
 * - continueWizard(): Navigate to wizard page with restored state
 * - cancelWizard(): Remove wizard state and visual indicators
 * - activeWizardStates: Reactive computed property for wizard detection
 *
 * VISUAL INDICATORS:
 * Categories with active wizards display:
 * - Magic wand icon with animated notification dot
 * - Blue left border and background tint
 * - Enhanced menu items (Continue/Cancel Wizard)
 *
 * COMPONENT ARCHITECTURE:
 * Categories (this) → CategoryList → CategoryItem → CategoryActions
 * All components are wizard-aware and pass down wizard state/functions
 *
 * STORE INTEGRATION:
 * - Legacy: VueX for categories, pagination, active states
 * - New: Pinia productWizardStore for wizard persistence
 * - Hybrid: Both stores work together for seamless UX
 *
 * @see CategoryList.vue - Handles category rendering with wizard props
 * @see CategoryItem.vue - Individual category with wizard indicators
 * @see productWizard.js - Pinia store for wizard state management
 */

import { PrintProductImport, CategoryRemoveItem, CustomProductCategoryForm } from "#components";
import { mapState, mapMutations, mapActions } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";
import CategoryHeader from "./components/CategoryHeader.vue";
import AssortmentTypeSelector from "./components/AssortmentTypeSelector.vue";
import CategoryFilter from "./components/CategoryFilter.vue";
import CategoryList from "./components/CategoryList.vue";
import CategorySidePanel from "./components/CategorySidePanel.vue";
import { markRaw } from "vue";

export default {
  name: "PrintProductCategoriesV2",
  components: {
    CategoryHeader,
    AssortmentTypeSelector,
    CategoryFilter,
    CategoryList,
    CategorySidePanel,
  },
  props: {
    selectingMode: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    "on-category-delete",
    "onMissingOptionsInMachinesIdentified",
    "onMissingGrsInCatalogueIdentified",
    "onMissingMaterialsInCatalogueIdentified",
    "onCategorySelected",
    "assortment-flag-changed",
  ],
  setup() {
    const { permissions, hasPermissionGroup, hasPermission } = usePermissions();
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    const productWizardStore = useProductWizardStore();

    return {
      permissions,
      hasPermissionGroup,
      hasPermission,
      api,
      handleError,
      handleSuccess,
      productWizardStore,
    };
  },
  data() {
    return {
      catType: "normal",
      necesaryCalcRefs: {
        format: true,
        material: true,
        weight: true,
        printingcolors: true,
      },
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit"),
              classes: "",
              show: false,
            },
            {
              action: "view_all",
              icon: "pallet",
              title: this.$t("view products"),
              classes: "",
              show: false,
            },
            {
              action: "export",
              icon: "file-export",
              title: this.$t("export"),
              classes: "",
              show: false,
            },
            {
              action: "import",
              icon: "file-import",
              title: this.$t("import"),
              classes: "",
              show: false,
            },
            {
              action: "delete",
              icon: "trash",
              title: this.$t("delete"),
              classes: "text-red-500",
              show: false,
            },
            {
              action: "continue_wizard",
              icon: "magic",
              title: this.$t("Continue Wizard"),
              classes: "text-blue-500",
              show: false,
            },
            // {
            //   action: "cancel_wizard",
            //   icon: "times",
            //   title: this.$t("Cancel Wizard"),
            //   classes: "text-orange-500",
            //   show: false,
            // },
          ],
        },
      ],
      filter: "",
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.categories,
      pagination: (state) => state.pagination.pagination,
      custom_categories: (state) => state.product.custom_categories,
      active_category: (state) => state.product.active_category,
      selected_category: (state) => state.product.selected_category,
      active_custom_category: (state) => state.product.active_custom_category,
      boops: (state) => state.product.boops,
      suppliers: (state) => state.suppliers.suppliers,
      assortment_flag: (state) => state.product.assortment_flag,
      component: (state) => state.product.component,
      cart_flag: (state) => state.cart.cart_flag,
      compare_flag: (state) => state.compare.flag,
    }),
    // Permissions
    maySelectCategory() {
      return this.hasPermissionGroup(this.permissions["print-assortments"].groups.categorySelect);
    },
    theCategories() {
      if (this.assortment_flag === "print_product") {
        return this.categories;
      } else {
        return this.custom_categories.map((category) => ({
          ...category,
          display_name: category.name,
        }));
      }
      // return this.assortment_flag === "print_product" ? this.categories : this.custom_categories;
    },
    rawComponent() {
      switch (this.component) {
        case "PrintProductImport":
          return markRaw(PrintProductImport);
        case "CategoryRemoveItem":
          return markRaw(CategoryRemoveItem);
        case "CustomProductCategoryForm":
          return markRaw(CustomProductCategoryForm);
        default:
          return h("div");
      }
    },
    shownCategories() {
      return this.categories.filter((category) => {
        return category.name.toLowerCase().includes(this.filter.toLowerCase());
      });
    },
    shownCustomCategories() {
      return this.custom_categories.filter((category) => {
        return category.name.toLowerCase().includes(this.filter.toLowerCase());
      });
    },
    incompleteCalcRefs() {
      return Object.values(this.necesaryCalcRefs).some((value) => value === false);
    },
    /**
     * NEW WIZARD FLOW: Reactive wizard states from localStorage
     *
     * Computed property that provides real-time access to all active wizard
     * states from the Pinia store. This ensures the UI updates automatically
     * when wizard states are created, modified, or deleted.
     *
     * @returns {Array} Array of active wizard state objects
     *
     * REACTIVITY:
     * - Automatically updates when localStorage changes
     * - Triggers re-render of CategoryItem visual indicators
     * - Enables dynamic menu item visibility
     * - Supports real-time wizard state management
     *
     * PASSED TO:
     * - CategoryList component as prop
     * - Used by CategoryItem for wizard detection
     */
    activeWizardStates() {
      return this.productWizardStore.getActiveWizardStates();
    },
  },
  watch: {
    active_custom_category(v) {
      return v;
    },
    assortment_flag: {
      immediate: true,
      handler() {
        this.$emit("assortment-flag-changed", this.assortment_flag);
        this.setVisibleMenuItems();
      },
    },
    categories: {
      immediate: true,
      handler() {
        /**
         * proces url params
         * Checks if the URL contains a query parameter for 'cat'.
         * If it does, it finds the corresponding category in `this.theCategories`.
         * If found, it selects the category based on the `assortment_flag`.
         */
        const route = useRoute();
        if (route.query.cat) {
          const targetCategory = this.theCategories.find((c) => c.slug === route.query.cat);
          if (targetCategory) {
            if (this.assortment_flag === "print_product") {
              this.selectCategory(targetCategory, true);
            } else {
              this.selectCustomCategory(targetCategory, true);
            }
          }
        }
      },
    },
  },
  async created() {
    /**
     * Resets the selected category, active custom category, active category, and boops to their default states.
     * Also sets the visible menu items.
     *
     * - `this.set_selected_category({})`: Resets the selected category to an empty object.
     * - `this.set_active_custom_category({})`: Resets the active custom category to an empty object.
     * - `this.set_active_category([])`: Resets the active category to an empty array.
     * - `this.set_boops("")`: Resets the boops to an empty string.
     * - `this.setVisibleMenuItems()`: Updates the visible menu items based on the current state.
     */
    this.set_selected_category({});
    this.set_active_custom_category({});
    this.set_active_category([]);
    this.set_boops("");
    this.get_suppliers();
    this.setVisibleMenuItems();
  },
  methods: {
    ...mapMutations({
      set_active_category: "product/set_active_category",
      set_active_custom_category: "product/set_active_custom_category",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_assortment_flag: "product/set_assortment_flag",
      set_component: "product/set_component",

      set_selected_category: "product_wizard/set_selected_category",
      set_wizard_type: "product_wizard/set_wizard_type",
      set_wizard_component: "product_wizard/set_wizard_component",

      set_cart_flag: "cart/set_cart_flag",

      set_boops: "product/set_boops",
    }),
    ...mapActions({
      get_suppliers: "suppliers/get_suppliers",
      get_categories: "product/get_categories",
      get_custom_categories: "product/get_custom_categories",
      get_custom_products: "product/get_custom_products",
    }),
    setVisibleMenuItems() {
      // Reset all to false initially to handle dynamic changes correctly
      this.menuItems[0].items.forEach((item) => (item.show = false));

      // Check if user has permission
      const canUpdateCategories = this.hasPermission("print-assortments-categories-update");
      const canListProducts = this.hasPermission("print-assortments-products-list");
      const canListBoxes = this.hasPermission("print-assortments-boxes-list");
      const canListOptions = this.hasPermission("print-assortments-options-list");
      const canListMargins = this.hasPermission("margins-list");
      const canDeleteCategories = this.hasPermission("print-assortments-categories-delete");

      // Determine visibility based on permissions and state
      if (canUpdateCategories && canListBoxes && canListOptions && canListMargins) {
        this.menuItems[0].items[0].show = true; // Edit
      }
      if (canListProducts && this.assortment_flag === "print_product") {
        this.menuItems[0].items[1].show = true; // View Products
      }
      if (canListProducts && canListBoxes && canListOptions) {
        this.menuItems[0].items[2].show = true; // Export
      }
      if (canUpdateCategories) {
        this.menuItems[0].items[3].show = true; // Import
      }
      if (canDeleteCategories) {
        // Show delete only if permission exists
        this.menuItems[0].items[4].show = true; // Delete
      }

      // Wizard actions are set dynamically per category in CategoryList component
    },
    handleCategoryDelete(category) {
      this.$emit("on-category-delete", category);
    },

    selectCategory(category, fromUrl = false) {
      if (!fromUrl) {
        const router = useRouter();
        router.replace({ query: {} });
      }

      if (this.hasPermissionGroup(this.permissions["print-assortments"].groups.categorySelect)) {
        if (!category.price_build.collection || this.assortment_flag === "custom_product") {
          this.menuItems[0].items[2].show = false;
          this.menuItems[0].items[3].show = false;
        } else {
          if (
            this.hasPermission("print-assortments-products-list") &&
            this.hasPermission("print-assortments-boxes-list") &&
            this.hasPermission("print-assortments-options-list")
          ) {
            this.menuItems[0].items[2].show = true;
          }
          if (this.hasPermission("print-assortments-categories-update")) {
            this.menuItems[0].items[3].show = true;
          }
        }
        this.api
          .get(`categories/${category.slug}`)
          .then((response) => {
            const cat = response.data;
            this.set_active_category([cat.id, cat.slug, cat.name]);
            this.set_selected_category(cat);
            this.set_boops(cat?.boops[0]);
            if (cat.price_build?.full_calculation) {
              this.checkCalcRefs(cat?.boops[0]?.boops);
              this.checkCatalogue(cat?.boops[0]?.boops);
              this.checkMachine(cat);
            }
            this.$emit("onCategorySelected", { cat: cat, type: "category", fromUrl: fromUrl });
          })
          .catch((error) => {
            this.handleError(error);
          });
      }
    },
    checkCalcRefs(boxes) {
      // Reset object
      this.necesaryCalcRefs = {
        format: false,
        material: false,
        weight: false,
        printingcolors: false,
      };

      // process each boop to determine wether the needed calcrefs are added
      // so we can guide the user into shaping its assortment with prices
      if (boxes) {
        boxes.forEach((box) => {
          if (box.calc_ref) {
            switch (box.calc_ref) {
              case "format":
                this.necesaryCalcRefs.format = true;
                break;

              case "material":
                this.necesaryCalcRefs.material = true;
                break;

              case "weight":
                this.necesaryCalcRefs.weight = true;
                break;

              case "printing_colors":
                this.necesaryCalcRefs.printingcolors = true;
                break;

              default:
                break;
            }
          }
        });
      }
    },
    async checkCatalogue(boops) {
      await this.api
        .get(`catalogues`)
        .then((catalogue) => {
          const missingMaterials = [];
          const missingGrs = [];

          if (boops) {
            boops.forEach((boop, boopIndex) => {
              if (boop.calc_ref === "material" && Array.isArray(boops?.ops)) {
                boop.ops.forEach((op, opIndex) => {
                  const material = catalogue.data.find((item) => item.material === op.name);
                  if (!material) {
                    missingMaterials.push({
                      box: boopIndex,
                      option: opIndex,
                      optionName: op.name,
                    });
                  }
                });
              }
              if (boop.calc_ref === "weight" && Array.isArray(boops?.ops)) {
                boop.ops.forEach((op, opIndex) => {
                  const grs = catalogue.data.find(
                    // (item) => item.grs.toLowerCase() === op.name.toLowerCase().replace("grs", "gr"), // catalogue converts gr to grs, sow e need to check for both
                    (item) => {
                      if (
                        item.grs.toLowerCase() === op.name.toLowerCase() ||
                        item.grs === op.name ||
                        item.grs.toLowerCase() === op.name.toLowerCase().replace("grs", "gr") ||
                        item.grs.toLowerCase() === op.name.toLowerCase().replace("grs", "gsm") ||
                        item.grs.toLowerCase() === op.name.toLowerCase().replace("gr", "gsm")
                      ) {
                        return item.grs;
                      }
                    },
                  );
                  if (!grs) {
                    missingGrs.push({
                      box: boopIndex,
                      option: opIndex,
                      optionName: op.name,
                    });
                  }
                });
              }
            });
          }

          this.$emit("onMissingMaterialsInCatalogueIdentified", missingMaterials);
          this.$emit("onMissingGrsInCatalogueIdentified", missingGrs);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    async checkMachine(cat) {
      const machines = await this.api.get("machines").catch((error) => this.handleError(error));
      const missingOptions = [];
      if (cat?.boops[0]?.boops) {
        cat.boops[0].boops.forEach((boop, boopIndex) => {
          if (boop.calc_ref === "printing_colors") {
            boop.ops.forEach((op, opIndex) => {
              const machineItem = machines.data.some((machine) =>
                machine.options.some((color) => color.id === op.id),
              );

              if (!machineItem) {
                missingOptions.push({
                  box: boopIndex,
                  option: opIndex,
                  optionName: op.name,
                });
              }
            });
          }
        });
      }

      this.$emit("onMissingOptionsInMachinesIdentified", missingOptions);
    },
    selectCustomCategory(category) {
      if (this.hasPermission("custom-assortments-products-read")) {
        this.get_custom_products({ cat_id: category.id });
        this.set_active_custom_category(category);
      }
    },
    activateModal() {
      this.set_wizard_type("");
      this.set_wizard_component("AddProductOverview");
    },
    activateDetails(category) {
      const nuxturl = `/assortment/details?cat=${category.slug}`;
      this.set_active_collection("");
      this.set_active_category([category.id, category.slug, category.name]);
      this.$router.push(nuxturl);
    },
    calcMethod(category) {
      if (category?.price_build?.full_calculation) {
        return {
          icon: "calculator",
          tooltip: this.$t("this category uses the FULL CALCULATION for pricing"),
        };
      } else if (category?.price_build?.semi_calculation) {
        return {
          icon: "box-full",
          tooltip: this.$t("this category uses the SEMI CALCULATION for pricing"),
        };
      } else {
        return {
          icon: "album-collection",
          tooltip: this.$t("this category uses the COLLECTION method for pricing"),
        };
      }
    },

    /**
     * HYBRID SYSTEM: Processes both legacy and wizard menu actions
     *
     * Handles menu item clicks from category context menus. Supports both
     * legacy category operations and new wizard flow actions.
     *
     * @param {String} event - Menu item action identifier
     * @param {Object} category - Category object for the menu action
     *
     * LEGACY ACTIONS:
     * - edit: Open category details/edit form
     * - view_all: Navigate to category products listing
     * - export: Export category products to Excel
     * - import: Open product import modal
     * - delete: Open category deletion confirmation
     *
     * NEW WIZARD ACTIONS:
     * - continue_wizard: Resume interrupted wizard session
     * - cancel_wizard: Permanently remove wizard state
     *
     * WIZARD MENU VISIBILITY:
     * Wizard actions only appear when hasActiveWizard(category) returns true.
     * This is controlled by CategoryItem.categoryMenuItems computed property.
     */
    menuItemClicked(event, category) {
      switch (event) {
        // LEGACY ACTIONS
        case "edit":
          if (this.assortment_flag === "print_product") {
            this.editCategoryInWizard(category);
          } else {
            this.set_component("CustomProductCategoryForm");
          }
          break;

        case "view_all":
          this.$router.push("/assortment/category-products");
          break;

        case "export":
          this.api
            .post(`categories/${this.active_category[1]}/products/export`, {
              type: "xlsx",
            })
            .then((response) => this.handleSuccess(response))
            .catch((error) => this.handleError(error));
          break;

        case "import":
          this.set_component("PrintProductImport");
          break;

        case "delete":
          this.set_component("CategoryRemoveItem");
          break;

        // NEW WIZARD ACTIONS
        case "continue_wizard":
          this.continueWizard(category);
          break;

        case "cancel_wizard":
          this.cancelWizard(category);
          break;

        default:
          break;
      }
    },
    close() {
      this.set_component("");
    },
    switchAssortmentType(type) {
      if (type === "print_product") {
        this.get_categories(
          this.pagination && this.pagination.page ? this.pagination.page : 1,
        ).then(() => {
          this.set_assortment_flag("print_product");
        });
      } else {
        this.get_custom_categories({
          per_page: 99999,
          page: this.pagination && this.pagination.page ? this.pagination.page : 1,
        }).then(() => {
          this.set_assortment_flag("custom_product");
        });
      }
    },

    handleEditItem(category) {
      if (this.assortment_flag === "print_product") {
        // Check if there's an active wizard for this category
        if (this.hasActiveWizard(category)) {
          // Continue the existing wizard
          this.continueWizard(category);
        } else {
          // Navigate to wizard in edit mode with category data
          this.editCategoryInWizard(category);
        }
      } else {
        this.set_component("CustomProductCategoryForm");
      }
    },

    /**
     * Load wizard with existing category data for editing
     * Creates a temporary wizard state with all category data pre-populated
     * but without marking it as an "active wizard" (no localStorage persistence)
     *
     * @param {Object} category - Category to edit
     */
    async editCategoryInWizard(category) {
      try {
        // Fetch full category data including boops
        const response = await this.api.get(`categories/${category.slug}`);
        const fullCategoryData = response.data;

        // Pre-populate wizard store with category data
        this.productWizardStore.initializeEditMode(category.id, {
          categorySearch: {
            selectedCategory: fullCategoryData,
            selected_category: fullCategoryData,
          },
          editBoops: {
            selectedBoops: fullCategoryData.boops || [],
            divided: fullCategoryData.divided || false,
          },
          calcReferences: {
            boops: fullCategoryData.boops || [],
          },
          // Add other step data as needed
        });

        // Navigate to wizard in edit mode
        this.$router.push(`/assortment/add-products?edit=${category.slug}`);
      } catch (error) {
        console.error("Failed to load category data for editing:", error);
        this.handleError(error);
      }
    },

    handleCustomCategoryUpdate() {
      this.set_assortment_flag("print_product");
      this.set_component("");
      this.get_custom_categories({
        per_page: 99999,
      }).then(() => {
        this.set_assortment_flag("custom_product");
      });
    },

    /**
     * NEW WIZARD FLOW: Check if a category has an active wizard state
     *
     * Checks localStorage for saved wizard states that match this category.
     * Used for displaying visual indicators and enabling wizard menu items.
     *
     * @param {Object} category - Category object with id/slug properties
     * @returns {Boolean} - True if category has unfinished wizard state
     *
     * MATCHING LOGIC:
     * - wizard.categoryId === category.id (primary match on category ID)
     * - wizard.categorySlug === category.slug (slug-based match)
     * - wizard.id === category.id (fallback for older wizard states)
     * - wizard.id === category.slug (fallback for slug-based IDs)
     */
    hasActiveWizard(category) {
      const activeWizards = this.productWizardStore.getActiveWizardStates();
      const result = activeWizards.some(
        (wizard) =>
          wizard.categoryId === category.id ||
          wizard.categorySlug === category.slug ||
          wizard.id === category.id ||
          wizard.id === category.slug,
      );

      // Debug logging to help identify matching issues
      if (result) {
        console.log(`[hasActiveWizard] Match found for category:`, {
          categoryId: category.id,
          categorySlug: category.slug,
          matchingWizard: activeWizards.find(
            (w) =>
              w.categoryId === category.id ||
              w.categorySlug === category.slug ||
              w.id === category.id ||
              w.id === category.slug,
          ),
        });
      }

      return result;
    },

    /**
     * NEW WIZARD FLOW: Get active wizard data for a specific category
     *
     * Retrieves the complete wizard state object for recovery/continuation.
     * Used internally by continueWizard() and cancelWizard() methods.
     *
     * @param {Object} category - Category object to find wizard for
     * @returns {Object|undefined} - Wizard state object or undefined if none found
     */
    getActiveWizard(category) {
      const activeWizards = this.productWizardStore.getActiveWizardStates();
      return activeWizards.find(
        (wizard) =>
          wizard.categoryId === category.id ||
          wizard.categorySlug === category.slug ||
          wizard.id === category.id ||
          wizard.id === category.slug,
      );
    },

    /**
     * NEW WIZARD FLOW: Continue interrupted wizard session
     *
     * Navigates to the wizard page with the ?continue parameter to restore
     * the exact wizard state where the user left off. The wizard page will
     * load the saved state from localStorage and resume from the correct step.
     *
     * @param {Object} category - Category with active wizard state
     *
     * NAVIGATION FLOW:
     * 1. Get wizard data for category
     * 2. Navigate to /assortment/add-products?continue={wizardId}
     * 3. Wizard page loads state from localStorage
     * 4. User continues from where they left off
     */
    continueWizard(category) {
      const wizard = this.getActiveWizard(category);
      if (wizard) {
        console.log("Continuing wizard for category:", category.name, "Wizard ID:", wizard.id);
        this.$router.push(`/assortment/add-products?continue=${wizard.id}`);
      }
    },

    /**
     * NEW WIZARD FLOW: Cancel and remove wizard state
     *
     * Permanently removes the wizard state from localStorage and triggers
     * UI updates to hide visual indicators. This action cannot be undone.
     *
     * @param {Object} category - Category to cancel wizard for
     *
     * CLEANUP PROCESS:
     * 1. Find active wizard for category
     * 2. Delete wizard state from localStorage
     * 3. Remove from active wizards list
     * 4. Force Vue reactivity update to hide indicators
     * 5. User sees normal category appearance
     */
    cancelWizard(category) {
      const wizard = this.getActiveWizard(category);
      if (wizard) {
        this.productWizardStore.deleteWizardState(wizard.id);
        console.log("Cancelled wizard for category:", category.name);

        // Force reactivity update to hide wizard indicators
        this.$forceUpdate();
      }
    },
  },
};
</script>
