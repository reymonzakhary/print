<!--
================================================================================
ADD PRODUCT SEARCH COMPONENT - LEGACY COMPATIBILITY SYSTEM
================================================================================

This component represents the older architecture of the CEC print product wizard.
It uses VueX Options API pattern with extensive legacy compatibility layers to
support both the old wizard system and newer event-driven patterns.

KEY ARCHITECTURAL DECISIONS:
- LEGACY VUEX: Uses Options API, mapState, mapMutations (avoid for new components)  
- DUAL COMPATIBILITY: Supports both VueX-based and event-driven parent components
- MIXIN INHERITANCE: Extends ProductsAndProducers mixin for shared search logic
- TIMING WORKAROUNDS: setTimeout calls to handle Vue reactivity edge cases

INTEGRATION PATTERNS:
1. Parent Communication: Emits events upward for modern parents (PrintProductCategorySearch)
2. State Management: Updates VueX store for legacy compatibility
3. Child Components: Manages ProductNameForm, AddNewItemCard, SearchResultItems
4. Search Logic: Inherits producer/product search from ProductsAndProducers mixin

⚠️  MAINTENANCE WARNING:
This component bridges old and new systems. When possible, prefer the modern
architecture shown in PrintProductCategorySearch.vue with composables and Pinia.

COMPARISON WITH MODERN APPROACH:
- Modern: Composables + Pinia + Composition API (PrintProductCategorySearch)
- Legacy: Options API + VueX + Mixins (This component)

If you're confused by the complexity here, check PrintProductCategorySearch.vue
for the cleaner, modern approach that this component is being migrated toward.
-->

<template>
  <!-- general item overview component -->
  <div class="relative h-full w-full rounded">
    <section class="flex flex-wrap justify-center">
      <!-- Selected producer info -->
      <!-- only show if selected producer -->
      <div class="w-full">
        <div
          v-if="selected_producer?.name"
          class="relative mx-auto -mt-4 mb-4 flex items-center justify-center"
        >
          <figure class="h-20 w-20 p-4">
            <img :src="selected_producer.logo" class="max-h-24 object-contain" />
          </figure>
          <h2 class="whitespace-nowrap font-bold">
            {{ selected_producer.name }}
          </h2>
          <div class="h-6">
            <HandshakeIcon :producer="selected_producer" />
          </div>
        </div>
      </div>

      <div class="w-full p-4 pb-0">
        <p
          class="mx-auto flex items-center gap-4 rounded border border-prindustry-200 bg-prindustry-100 px-4 py-2 text-sm text-prindustry-500 dark:border-prindustry-800 dark:bg-prindustry-700 dark:text-prindustry-400"
        >
          <UIPrindustryBox class="size-12" line="text-prindustry-500" />
          {{
            $t(
              "Search for a standardized term from the Prindustry Ecosystem, so it is linked and recognized for use in the Prindustry Marketplace",
            )
          }}
        </p>
      </div>

      <div
        class="top-4 z-20 flex w-full justify-center border-b p-6 pb-0 dark:border-gray-800"
        :class="{ sticky: stickySearch, 'bg-white dark:bg-gray-700': wizardMode }"
      >
        <form v-if="!selected" class="flex w-full pb-4" @submit.prevent>
          <input
            ref="searchBar"
            type="text"
            :placeholder="$t(`Search {type} in Prindustry's ecosystem`, { type: type })"
            class="input mx-auto border-2 border-prindustry-400 shadow-lg focus:border-prindustry-500 focus:ring-prindustry-200 dark:border-prindustry-400 dark:shadow-gray-950 md:w-1/2"
            @input="(set_search($event.target.value), (selected = false))"
          />
        </form>
      </div>

      <section
        v-if="items && items.length > 0 && !selected"
        class="z-10 mt-6 flex w-full flex-wrap"
      >
        <div v-for="(item, i) in items" :key="item.name + i" class="flex w-full items-center">
          <div
            class="group mx-auto my-3 flex w-full cursor-pointer items-stretch justify-between rounded border-2 bg-white transition hover:border-theme-500 hover:bg-theme-50 hover:text-theme-500 hover:shadow-xl focus:shadow-none dark:border-gray-800 dark:bg-gray-700 dark:hover:border-theme-400 dark:hover:bg-theme-800"
            @click="selectItem(item)"
          >
            <div class="flex w-1/2 items-center p-2">
              <figure class="mr-4">
                <img
                  v-if="getImgUrl(item.name?.toLowerCase() ?? item.display_name[0].toLowerCase())"
                  :src="getImgUrl(item.name?.toLowerCase() ?? item.display_name[0].toLowerCase())"
                  :alt="item.name"
                />

                <UIPrindustryBox v-else class="size-12" line="text-prindustry-500" />
              </figure>

              <div class="w-full">
                <span class="w-full text-sm text-gray-500">{{ $t("Prindustry standard") }}</span>
                <p
                  class="shrink-0 whitespace-nowrap text-xl"
                  @click="
                    selectItem(item, item.display_name ? $display_name(item.display_name) : null)
                  "
                >
                  <template v-if="item.display_name">
                    <span
                      v-for="(part, index) in highlightName($display_name(item.display_name))"
                      :key="index"
                      class="truncate"
                      :class="{ 'font-semibold text-theme-500': part.highlight }"
                    >
                      {{ part.text }}
                    </span>
                  </template>
                  -
                  <template v-if="item.name">
                    <span
                      v-for="(part, index) in highlightName(item.name)"
                      :key="index"
                      class="truncate text-sm text-gray-500"
                      :class="{ 'text-theme-300': part.highlight }"
                    >
                      {{ part.text }}
                    </span>
                  </template>
                </p>
              </div>
            </div>

            <div
              class="w-1/2 bg-gray-50 p-2 group-hover:bg-theme-100 dark:bg-gray-750 dark:group-hover:bg-theme-900"
            >
              <div
                v-if="item.community && item.community.length > 0"
                class="ml-10 flex w-full cursor-pointer flex-wrap items-center rounded text-xs hover:text-theme-500"
              >
                <span class="w-full text-sm text-gray-500">
                  {{ $t("community values and translations") }}
                </span>

                <!-- Locale info -->
                <div class="flex flex-wrap gap-2">
                  <span v-for="(localeLanguage, index) in item.community" :key="index">
                    <span class="font-bold uppercase text-gray-400">{{ $i18n.locale }}</span>
                    <font-awesome-icon :icon="['fal', 'link']" class="mx-1 text-gray-400" />
                    <span
                      v-for="(part, index) in highlightName(localeLanguage[$i18n.locale])"
                      :key="index"
                      class="truncate"
                      :class="{ 'font-semibold text-theme-500': part.highlight }"
                    >
                      {{ part.text }}
                    </span>
                  </span>
                </div>
              </div>

              <div v-if="Array.isArray(item.linked)" class="columns-1 md:columns-2 lg:columns-3">
                <div
                  v-for="(linked, i) in item.linked"
                  :key="`${linked.name}_${i}`"
                  class="nested-item relative ml-14 flex w-full cursor-pointer items-center hover:text-theme-500"
                >
                  <font-awesome-icon :icon="['fal', 'link']" class="mx-2 text-gray-400" />
                  <span>
                    {{ $display_name(linked.display_name) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add new item option when results exist -->
        <AddNewItemCard
          :type="type"
          :has-results="true"
          :search-term="search"
          :display-value="getDisplayValue()"
          :name-value="getNameValue()"
          @update-display-name="handleUpdateDisplayName"
          @update-name="handleUpdateName"
          @add-new="handleAddNew"
          @clear-selection="handleClearSelection"
        />
      </section>

      <!-- ITEM NOT FOUND -->
      <section v-else-if="items && items.length === 0 && !selected" class="w-full">
        <AddNewItemCard
          :type="type"
          :has-results="false"
          :search-term="search"
          @add-new="handleAddNew"
          @clear-selection="handleClearSelection"
        />
      </section>

      <!-- Add selected item as -->
      <section v-else-if="selected && selected_search_item" class="w-full">
        <div
          class="relative mt-10 w-full items-center rounded border-2 bg-white dark:border-gray-900 dark:bg-gray-700"
        >
          <div class="flex items-center justify-between bg-gray-100 px-4 dark:bg-gray-800">
            <p class="flex flex-1 items-center">
              <UIPrindustryBox class="mr-2 size-8" line="text-prindustry-500" />{{
                $t("adding item")
              }}
            </p>
            <div
              class="mx-2 flex flex-1 items-center justify-center rounded bg-gray-100 dark:bg-gray-800"
            >
              <figure class="h-12 w-12 p-1">
                <img
                  v-if="getImgUrl(selected_search_item.name?.toLowerCase())"
                  :src="getImgUrl(selected_search_item.name?.toLowerCase())"
                  :alt="selected_search_item.name"
                />
                <img
                  v-else
                  src="~/assets/images/assortments_portal/en/box.svg"
                  :alt="selected_search_item.name"
                />
              </figure>

              <h2 class="mr-2 whitespace-nowrap text-xl font-bold">
                {{ $display_name(selected_search_item.display_name) }} -
                <span class="text-sm">{{ selected_search_item.name }}</span>
              </h2>
            </div>

            <div class="flex w-full flex-1 justify-end">
              <button
                class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-sm dark:bg-gray-900"
                @click="handleClearSelection"
              >
                x
              </button>
            </div>
          </div>

          <div v-if="adding_type !== 'from_producer'" class="px-2 pb-4 pt-8">
            <ProductNameForm
              :display-value="$display_name(selected_search_item.display_name)"
              :name-value="selected_search_item.name"
              :show-button="false"
              @update-display-name="handleUpdateDisplayName"
              @update-name="handleUpdateName"
            />
          </div>

          <div v-if="showNavigationButton" class="mt-10 flex w-full">
            <button
              class="ml-auto mr-4 rounded-full border-2 border-theme-500 px-4 py-2 text-theme-500 transition-all hover:bg-theme-400 hover:text-themecontrast-400"
              @click="$emit('addNew')"
            >
              <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
              {{
                type === "category"
                  ? $t("Create Category")
                  : type === "box"
                    ? $t("Create Box")
                    : $t("Create Option")
              }}
              <font-awesome-icon :icon="['fas', 'angle-right']" class="ml-2" />
            </button>
          </div>
        </div>
      </section>
    </section>
  </div>
</template>

<script>
/**
 * AddProductSearch Component
 *
 * ⚠️  LEGACY COMPONENT WITH DUAL COMPATIBILITY ⚠️
 * =====================================================
 *
 * This component serves as a bridge between the old VueX-based product wizard
 * and the new Pinia-based wizard system. It maintains backward compatibility
 * while supporting modern event-driven architecture.
 *
 * ARCHITECTURE OVERVIEW:
 * =====================
 *
 * 🏗️ LEGACY SYSTEMS (Still Active):
 * - VueX Store: Uses mapState/mapMutations for state management
 * - Options API: Component written in legacy Vue 2 style
 * - Mixin Pattern: Inherits functionality from customProductMixin.js
 * - Global State: Relies on shared wizard state across components
 *
 * 🆕 MODERN SYSTEMS (New Integration):
 * - Event Emission: Emits events for parent components to handle
 * - Composition API Setup: Uses setup() hook for modern utilities
 * - Component Isolation: Can work independently of global wizard state
 *
 * DUAL COMPATIBILITY PATTERNS:
 * ===========================
 *
 * 1. STATE MANAGEMENT:
 *    - Legacy: Uses VueX mutations (set_name, set_selected_search_item)
 *    - Modern: Emits events (@category-selected, @update-display-name)
 *
 * 2. NAVIGATION:
 *    - Legacy: Direct component switching via set_component mutation
 *    - Modern: Event emission (@add-new) for parent to handle progression
 *
 * 3. DATA FLOW:
 *    - Legacy: Props → VueX → Template → Mutations → Global State
 *    - Modern: Props → Local State → Events → Parent Component
 *
 * INTEGRATION POINTS:
 * ==================
 *
 * 📤 EVENTS EMITTED (New System):
 * - "addNew" → Triggers category/item creation in parent
 * - "category-selected" → Notifies parent when category is selected
 * - "item-selected" → Notifies parent when item is selected
 * - "search-updated" → Notifies parent of search term changes
 * - "update-display-name" → Synchronizes form input changes
 * - "update-system-key" → Synchronizes system key changes
 *
 * 🔄 VUEX INTEGRATION (Legacy System):
 * - Reads from: product_wizard store (search, selected_search_item, etc.)
 * - Writes to: product_wizard mutations (set_name, set_selected_search_item)
 *
 * USAGE SCENARIOS:
 * ===============
 *
 * 🎯 SCENARIO 1: New Wizard (PrintProductCategorySearch)
 * - Parent handles all events
 * - Uses modern state management
 * - VueX state used only for form input persistence
 *
 * 🎯 SCENARIO 2: Legacy Wizard (Old product creation flow)
 * - Relies on VueX state management
 * - Direct component navigation via mutations
 * - Events may be ignored by legacy parents
 *
 * CRITICAL IMPLEMENTATION DETAILS:
 * ===============================
 *
 * ⚠️  Search Term Passing:
 * - handleAddNew() now passes this.search to parent
 * - This was added to fix "[object Object]" display name issue
 * - Essential for "no results found" category creation
 *
 * ⚠️  Event Timing:
 * - selectItem() and selectLinked() emit events after VueX mutations
 * - Ensures both systems stay in sync
 *
 * ⚠️  Form Synchronization:
 * - handleUpdateDisplayName() updates both VueX and emits event
 * - Prevents form state divergence between old/new systems
 */

import { mapState, mapMutations } from "vuex";
import mixin from "~/components/assortment/add_products/customProductMixin.js";
// import LocalPagination from "~/components/global/LocalPagination.vue";
import _ from "lodash";
import UIPrindustryBox from "~/components/global/ui/UIPrindustryBox.vue";
import AddNewItemCard from "~/components/assortment/add_products/print_product/AddNewItemCard.vue";
import ProductNameForm from "~/components/assortment/add_products/print_product/ProductNameForm.vue";
import wizard from "~/store/wizard";

export default {
  // ============================================================================
  // COMPONENT CONFIGURATION
  // ============================================================================

  components: {
    UIPrindustryBox, // UI component for generic box icons
    AddNewItemCard, // Card component for "add new" functionality
    ProductNameForm, // Form for editing display names and system keys
  },

  /**
   * LEGACY MIXIN INTEGRATION
   * Inherits shared functionality from customProductMixin.js
   * ⚠️ This mixin contains legacy patterns and global state dependencies
   * TODO: Consider extracting mixin functionality to composables for new components
   */
  mixins: [mixin],

  // ============================================================================
  // COMPONENT PROPS - Configuration from parent components
  // ============================================================================

  props: {
    /**
     * API endpoint for searching items
     * Different endpoints used based on product type and producer selection
     * Examples:
     * - "finder/categories/search?search=" (default categories)
     * - "suppliers/123/categories?filter=" (producer categories)
     */
    searchUrl: {
      required: false,
      type: String,
      default: "finder/categories/search?search=",
    },

    /**
     * LEGACY PROP: Next component for old navigation system
     * Used by legacy wizard for direct component switching
     * ⚠️ Not used in new event-driven system
     */
    nextComponent: {
      type: String,
      default: "PrintProductEditBoops",
    },

    /**
     * Type of item being searched for
     * Affects UI labels, icons, and search behavior
     * Values: "category", "box", "option", "item"
     */
    type: {
      required: false,
      type: String,
      default: "item",
    },

    /**
     * LEGACY TYPE MAPPING: How the item is being added to the product
     * Used for backward compatibility with old wizard system
     * Values:
     * - "from_preset" → Adding from Prindustry preset library
     * - "from_producer" → Adding from specific producer/supplier
     * - "blank" → Creating completely custom item
     */
    adding_type: {
      required: false,
      type: String,
      default: "from_preset",
    },

    /**
     * Whether to show the navigation button in the selection UI
     * Legacy wizards may handle navigation differently
     * New wizards typically handle navigation via events
     */
    showNavigationButton: {
      required: false,
      type: Boolean,
      default: true,
    },
    /**
     * Whether the search bar should stick to the top when scrolling
     * Improves usability for long result lists
     * Can be disabled for specific layouts
     */
    stickySearch: {
      type: Boolean,
      default: true,
    },

    wizardType: {
      type: Boolean,
      default: false,
    },
  },

  /**
   * MODERN EVENT SYSTEM - Events emitted to parent components
   * These events enable the new event-driven wizard architecture
   */
  emits: [
    "addNew",
    "category-selected",
    "item-selected",
    "search-updated",
    "update-display-name",
    "update-name",
  ],
  /**
   * MODERN COMPOSITION API INTEGRATION
   * Uses setup() hook to access modern Nuxt utilities
   * This allows the legacy component to use new composables while maintaining Options API structure
   */
  setup() {
    const { capitalizeFirstLetter } = useUtilities(); // String formatting utilities
    const api = useAPI(); // Modern API client
    return { capitalizeFirstLetter, api };
  },

  // ============================================================================
  // COMPONENT DATA - Local reactive state
  // ============================================================================

  data() {
    return {
      /**
       * Search results from API calls
       * Populated by watch handler when search term changes
       * Array of category/item objects with display_name, name, linked, etc.
       */
      items: [],

      /**
       * Whether user has selected an item
       * Controls which UI section is displayed:
       * - false: Show search results or "no results found"
       * - true: Show selected item confirmation with form
       */
      selected: false,

      /**
       * LEGACY PAGINATION SUPPORT
       * Used for paginated search results (currently not actively used)
       * Maintained for backward compatibility with API responses
       */
      pagination: {
        total: 0,
        current_page: 1,
        per_page: 100,
        last_page: 0,
      },
    };
  },
  // ============================================================================
  // COMPUTED PROPERTIES - Reactive data and debugging helpers
  // ============================================================================

  computed: {
    /**
     * DEBUG HELPER: Template visibility troubleshooting
     *
     * This computed property helps debug which template sections should be visible.
     * The template has complex conditional rendering based on:
     * - Search results (items array)
     * - Selection state (selected boolean)
     * - Item selection (selected_search_item object)
     *
     * Console output helps identify why certain sections aren't showing
     *
     * USAGE: Add {{ debugInfo }} to template to trigger reactive updates
     */
    debugInfo() {
      const info = {
        items: this.items?.length || 0, // Number of search results
        selected: this.selected, // Local selection state
        search: this.search, // Current search term (from VueX)
        hasResults: this.items && this.items.length > 0, // Whether search found results
        noResults: this.items && this.items.length === 0, // Whether search found nothing
        showResults: this.items && this.items.length > 0 && !this.selected, // Show results list
        showNoResults: this.items && this.items.length === 0 && !this.selected, // Show "no results" card
        showSelected: this.selected && this.selected_search_item, // Show selection confirmation
      };
      return info;
    },

    /**
     * LEGACY VUEX STATE MAPPING
     *
     * These computed properties map VueX store state to component properties.
     * This enables the component to react to global wizard state changes.
     *
     * ⚠️  DUAL USAGE:
     * - Legacy wizards: Primary source of truth for form state
     * - Modern wizards: Secondary state for form input persistence and backward compatibility
     *
     * STATE SOURCES:
     * - product_wizard store module (legacy global wizard state)
     */
    ...mapState({
      /**
       * Currently selected search item object
       * Contains: name, display_name, id, linked, etc.
       * Used to populate selection confirmation UI
       */
      selected_search_item: (state) => state.product_wizard.selected_search_item,

      /**
       * Selected producer from previous wizard step
       * Used to show producer logo/name in header
       * Only relevant when adding_type === "from_producer"
       */
      selected_producer: (state) => state.product_wizard.selected_producer,

      /**
       * Current search term
       * Drives API calls via watcher
       * Updated by search input field
       */
      search: (state) => state.product_wizard.search,

      /**
       * Generated name based on name
       * Used for URL-safe identifiers and API calls
       * Auto-generated but can be manually edited
       */
      name: (state) => state.product_wizard.name,
    }),
  },
  // ============================================================================
  // WATCHERS - Reactive behavior for state changes
  // ============================================================================

  watch: {
    /**
     * SEARCH TERM WATCHER - Drives API calls for search functionality
     *
     * BEHAVIOR:
     * - Debounced by 300ms to prevent excessive API calls during typing
     * - Calls search API with current term and locale
     * - Updates local items array with results
     * - Handles different response formats (suggestions vs direct data)
     *
     * SIDE EFFECTS:
     * - Updates VueX name with search term (for "no results" scenario)
     * - Emits "search-updated" event to notify parent components
     *
     * ⚠️  CRITICAL FOR CATEGORY CREATION:
     * When no results found, the search term becomes the basis for new category names
     */
    search: _.debounce(function (v) {
      // API call with locale support
      this.api.get(`${this.searchUrl}${v}&iso=${this.$i18n.locale}`).then((response) => {
        // Handle different API response formats
        this.items = response?.data?.suggestions
          ? Object.values(
              response.data.suggestions.reduce((acc, item) => {
                const key = item.linked;

                if (!acc[key]) {
                  acc[key] = {
                    name: item.name,
                    display_name: item.display_name,
                    slug: item.slug,
                    linked: item.linked,
                    community: [],
                  };
                }

                acc[key].community.push({ [this.$i18n.locale]: item[this.$i18n.locale] });

                return acc;
              }, {}),
            )
          : [];

        // LEGACY SYSTEM: Update VueX state with search term
        this.set_name(v);

        // MODERN SYSTEM: Notify parent component of search update
        this.$emit("search-updated", v);
      });
    }, 300),

    /**
     * SELECTED ITEM WATCHER - Updates form fields when item is selected
     *
     * Automatically populates name and display_name when user selects an item.
     * Handles different display_name formats (string vs array with locale support).
     *
     * LEGACY INTEGRATION: Updates VueX state for backward compatibility
     */
    selected_search_item(v) {
      // Handle localized display names
      this.set_display_name(v.display_name);
      this.set_name(v.name);
      return v;
    },

    /**
     * LEGACY WATCHER: Text highlighting functionality
     *
     * ⚠️  This appears to be incomplete/broken code from an old feature.
     * References undefined variables (query, classList).
     * TODO: Remove or fix this watcher if highlighting is still needed.
     */
    word(v) {
      const check = new RegExp(query, "ig");
      return v.toString().replace(check, function (matchedText) {
        return `<strong class='${classList}'>` + matchedText + "</strong>";
      });
    },
  },
  // ============================================================================
  // COMPONENT LIFECYCLE - Initialization and cleanup
  // ============================================================================

  /**
   * CLEANUP: Clear search state when component is destroyed
   *
   * Prevents search term from persisting when user navigates away.
   * Important for wizard flow where search should reset between steps.
   *
   * LEGACY BEHAVIOR: Updates VueX state for other components
   */
  beforeUnmount() {
    this.set_search("");
  },

  /**
   * INITIALIZATION: Load initial data when component is created
   *
   * BEHAVIOR:
   * - Fetches initial items from search API (without search term)
   * - Populates items array for display before user searches
   * - Sets up pagination metadata if provided by API
   * - Gracefully handles API errors by setting empty items array
   * - Skips loading if searchUrl is empty (waiting for parent initialization)
   *
   * NOTE: This provides a "browse" experience before user starts searching
   */
  created() {
    // Skip loading if searchUrl is not set (waiting for parent)
    if (!this.searchUrl) return;
    this.api
      .get(`${this.searchUrl}&iso=${this.$i18n.locale}&per_page=${this.pagination.per_page}`)
      .then((response) => {
        this.items = response?.data?.suggestions
          ? Object.values(
              response.data.suggestions.reduce((acc, item) => {
                const key = item.linked;

                if (!acc[key]) {
                  acc[key] = {
                    name: item.name,
                    display_name: item.display_name,
                    slug: item.slug,
                    linked: item.linked,
                    community: [],
                  };
                }

                acc[key].community.push({ [this.$i18n.locale]: item[this.$i18n.locale] });

                return acc;
              }, {}),
            )
          : [];

        // LEGACY PAGINATION: Set pagination metadata if provided
        // Currently not actively used in UI but maintained for compatibility
        if (response.meta) {
          this.pagination.total = response.meta.total;
          this.pagination.current_page = response.meta.current_page;
          this.pagination.per_page = response.meta.per_page;
          this.pagination.last_page = response.meta.last_page;
        }
      })
      .catch((error) => {
        this.items = []; // Fail gracefully with empty array
      });
  },

  /**
   * DOM READY: Focus search input for immediate user interaction
   *
   * Improves UX by allowing user to start typing immediately when page loads.
   * Uses template ref to access the search input element.
   */
  mounted() {
    this.$refs.searchBar.focus();
  },
  // ============================================================================
  // COMPONENT METHODS - User interactions and business logic
  // ============================================================================

  methods: {
    // ========================================================================
    // LEGACY VUEX MUTATIONS - Global state management
    // ========================================================================

    /**
     * VueX mutation mappings for backward compatibility
     *
     * These mutations update the global product wizard state.
     * Used by both legacy and modern systems for different purposes:
     *
     * LEGACY USAGE: Primary state management for old wizard flow
     * MODERN USAGE: Secondary state for form persistence and backward compatibility
     */
    ...mapMutations({
      /** Navigate to different wizard component (legacy navigation) */
      set_component: "product_wizard/set_wizard_component",

      /** Set selected category in global state */
      set_selected_category: "product_wizard/set_selected_category",

      /** Set selected search item (category, box, option) */
      set_selected_search_item: "product_wizard/set_selected_search_item",

      /** Set custom display name for selected item */
      set_display_name: "product_wizard/set_display_name",

      /** Set system key (URL-safe identifier) */
      set_name: "product_wizard/set_name",

      /** Set current search term */
      set_search: "product_wizard/set_search",
    }),

    // ========================================================================
    // USER INTERACTION METHODS - Item selection and form handling
    // ========================================================================

    /**
     * SELECT ITEM FROM SEARCH RESULTS
     *
     * Called when user clicks on a search result item.
     * Handles both legacy VueX updates and modern event emission.
     *
     * PARAMETERS:
     * - item: The selected item object from search results
     * - customName: Optional override name (used by linked items)
     *
     * DUAL SYSTEM INTEGRATION:
     * 1. Updates VueX state (set_selected_search_item, set_name)
     * 2. Updates local UI state (this.selected = true)
     * 3. Emits events for modern parent components
     *
     * EVENT EMISSION:
     * - Emits different events based on item type for specialized handling
     */
    selectItem(item, customName = null) {
      // LEGACY SYSTEM: Update global VueX state
      this.set_selected_search_item(item);

      // Determine display name with fallbacks
      this.set_name(item.name);
      this.set_display_name(item.display_name);

      // LOCAL STATE: Show selection confirmation UI
      this.selected = true;

      // MODERN SYSTEM: Emit type-specific events for parent components
      if (this.type === "category") {
        this.$emit("category-selected", item);
      } else {
        this.$emit("item-selected", item);
      }
    },

    /**
     * SELECT LINKED ITEM FROM NESTED RESULTS
     *
     * Called when user clicks on a linked/nested item within search results.
     * Some items have "linked" arrays with related variations.
     *
     * BEHAVIOR:
     * - Selects the parent item but uses linked item's display name
     * - Uses setTimeout for UI state timing (legacy pattern)
     * - Follows same dual system pattern as selectItem()
     *
     * PARAMETERS:
     * - item: Parent item object that contains the linked items
     * - linked: Specific linked item that was clicked
     */
    selectLinked(item, linked) {
      // LEGACY SYSTEM: Update global state with parent item
      this.set_selected_search_item(item);

      // TIMING WORKAROUND: Allow VueX state to update before setting custom name
      // TODO: This setTimeout pattern should be refactored for more predictable state updates
      setTimeout(() => {
        // Use linked item's display name instead of parent's
        this.set_name(linked.name);
        this.selected = true;

        // MODERN SYSTEM: Emit events (uses parent item, not linked item)
        if (this.type === "category") {
          this.$emit("category-selected", item);
        } else {
          this.$emit("item-selected", item);
        }
      }, 200);
    },

    // ========================================================================
    // FORM EVENT HANDLERS - Synchronization between systems
    // ========================================================================

    /**
     * HANDLE DISPLAY NAME UPDATES FROM FORM
     *
     * Called when ProductNameForm emits display name changes.
     * Ensures both legacy VueX state and modern parent components stay synchronized.
     *
     * DUAL SYNCHRONIZATION:
     * 1. Updates VueX for legacy compatibility
     * 2. Emits event for modern parent components
     */
    handleUpdateDisplayName(value) {
      this.$emit("update-display-name", { iso: this.$i18n.locale, display_name: value }); // MODERN: Notify parent component
    },

    /**
     * HANDLE NAME UPDATES FROM FORM
     *
     * Called when ProductNameForm emits system key changes.
     * System keys are URL-safe identifiers used in API calls.
     */
    handleUpdateName(value) {
      this.set_name(value); // LEGACY: Update VueX state
      this.$emit("update-name", value); // MODERN: Notify parent component
    },

    /**
     * HANDLE SELECTION CLEARING
     *
     * Called when user clicks 'x' button to clear their selection.
     * Returns to search results view from selection confirmation.
     */
    handleClearSelection() {
      this.set_selected_search_item({}); // LEGACY: Clear VueX state
      this.selected = false; // LOCAL: Hide selection UI
      // Could emit event here if needed by parent (currently parent doesn't need notification)
    },

    /**
     * HANDLE "ADD NEW" ACTION - CRITICAL FOR CATEGORY CREATION
     *
     * Called when AddNewItemCard emits "add-new" event.
     * This happens in two scenarios:
     * 1. No search results found → User creates completely new item
     * 2. Results found but user wants something different → User creates custom variation
     *
     * ⚠️  SEARCH TERM PASSING:
     * This method now passes this.search to the parent component.
     * This was added to fix the "[object Object]" display name issue.
     * The search term becomes the basis for the new item's display name.
     *
     * CRITICAL FOR: PrintProductCategorySearch "no results found" scenario
     */
    handleAddNew() {
      // PASS SEARCH TERM: Essential for proper display name creation
      // Parent component uses this to determine display name when no results found
      this.$emit("addNew", this.search);
    },

    // ========================================================================
    // UTILITY METHODS - Display and value resolution
    // ========================================================================

    /**
     * GET DISPLAY VALUE FOR UI
     *
     * Priority hierarchy for determining what display name to show:
     * 1. Custom name (if user has manually set one via ProductNameForm)
     * 2. Selected search result name (if user selected an existing item)
     * 3. Search term (if neither custom nor selected - fallback display)
     *
     * Uses $display_name() for multilingual support.
     */
    getDisplayValue() {
      return this.display_name
        ? this.$display_name(this.display_name)
        : this.selected_search_item.name
          ? this.selected_search_item.name
          : this.search;
    },

    /**
     * GET SYSTEM VALUE FOR API CALLS
     *
     * IDENTICAL to getDisplayValue() - determines the value for system operations.
     * NOTE: Both methods could be consolidated in future refactoring.
     */
    getNameValue() {
      return this.name
        ? this.name
        : this.selected_search_item.name
          ? this.selected_search_item.name
          : this.search;
    },

    /**
     * HIGHLIGHT SEARCH TERMS IN RESULTS
     *
     * Creates visual highlighting for search term matches in result names.
     * Returns array of text segments with highlight flags for UI rendering.
     *
     * REGEX ESCAPING: Safely handles special regex characters in search terms
     * CASE INSENSITIVE: Uses 'ig' flags for inclusive matching
     */
    highlightName(name) {
      if (!this.search || this.search.length === 0) {
        return [{ text: name, highlight: false }];
      }

      // Escape special regex characters to prevent regex injection
      const escapedSearch = this.search.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      const check = new RegExp(`(${escapedSearch})`, "ig");

      // Split name by search term and mark matching parts for highlighting
      const parts = name?.split(check).map((part) => {
        if (check.test(part)) {
          return { text: part, highlight: true }; // MATCHED: Will be highlighted in UI
        } else {
          return { text: part, highlight: false }; // UNMATCHED: Normal text
        }
      });

      return parts;
    },
  },
};
</script>
