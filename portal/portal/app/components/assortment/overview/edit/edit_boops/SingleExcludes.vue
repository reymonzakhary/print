<template>
  <div class="mx-auto w-full p-4">
    <div v-if="boops" class="flex flex-wrap">
      <template v-for="(box, index) in boops" :key="index">
        <div
          v-if="selected_divider === box.divider || !divided"
          class="relative flex"
          :class="{
            'mx-1 my-4 flex rounded border border-gray-200 px-2 py-4 dark:border-gray-500': divided,
            'ml-0 rounded-l-none !border-l-0 !pl-0': boops[index - 1]?.divider === box.divider,
            'mr-0 rounded-r-none !border-r-0 !pr-0': boops[index + 1]?.divider === box.divider,
          }"
        >
          <div
            v-if="
              divided && (boops[index - 1]?.divider !== box.divider || (index === 0 && divided))
            "
            class="absolute mx-auto -mt-7 flex items-center bg-white px-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-800"
          >
            {{ box.divider }}

            <font-awesome-icon
              :icon="['fal', 'calculator']"
              class="fa-lg ml-4 mr-2 text-gray-400 dark:text-gray-500"
            />
            <span
              class="h-full truncate font-normal normal-case text-gray-500"
              :title="$t('calculation')"
            >
              {{ $t("calculation") }}
            </span>
          </div>

          <font-awesome-icon
            v-if="selected_box.id === box.id"
            :icon="['fas', 'play']"
            class="absolute top-1/2 -translate-y-1/2 rotate-180 transform text-theme-400"
            :class="{ '-left-[2px]': !divided, 'left-[6px]': divided }"
          />

          <nav
            class="m-2 w-full rounded border-2 p-2 dark:bg-gray-700"
            :class="
              checkIfEntireBox(box.divider, box.id)
                ? 'bg-gray-200'
                : selected_box.id === box.id
                  ? 'border-2 border-theme-400 bg-white'
                  : 'bg-white'
            "
          >
            <h2 class="pl-2 text-sm font-bold uppercase tracking-wide">
              {{ $display_name(box.display_name) }}
            </h2>

            <div
              class="my-2 h-8 w-full border-b px-2 text-theme-500 hover:cursor-pointer hover:text-theme-700"
              @click="Manage_single_entire_excludes(box.id, divided ? box.divider : null)"
            >
              <template v-if="!box.ops.find((op) => op.id === selected_option.id)">
                <font-awesome-icon
                  v-if="checkIfEntireBox(box.divider, box.id)"
                  :icon="['fas', 'times-square']"
                  class="mr-1 text-red-500"
                />
                <font-awesome-icon v-else :icon="['fal', 'square']" class="mr-1 text-theme-500" />
                {{ $t("exclude entire box") }}
              </template>
            </div>

            <ul>
              <li
                v-for="(item, idx) in box.ops"
                :key="idx"
                class="my-2 flex w-52 items-center rounded px-2"
                :class="{
                  'border border-theme-500 text-theme-500 hover:bg-white':
                    selected_option.id === item.id &&
                    box.id === selected_box.id &&
                    (!divided || selected_divider === box.divider),
                  'hover:bg-gray-200 dark:hover:bg-gray-800 cursor-pointer':
                    !box.ops.find((op) => op.id === selected_option.id) || selected_option.id === item.id,
                  'opacity-50 cursor-not-allowed':
                    box.ops.find((op) => op.id === selected_option.id) && selected_option.id !== item.id,
                }"
                @click.stop="(!box.ops.find((op) => op.id === selected_option.id) || selected_option.id === item.id) && set_single_excludes(box.id, item.id, divided ? box.divider : null)"
              >
                <div class="flex w-full items-center"
                  :class="{ 'cursor-pointer': !box.ops.find((op) => op.id === selected_option.id) || selected_option.id === item.id }"
                >
                  <template v-if="selected_option.id !== item.id">
                    <font-awesome-icon
                      v-if="checkState(box.divider, item.id)"
                      :icon="['fas', 'times-square']"
                      class="mr-2 text-red-500"
                    />
                    <font-awesome-icon
                      v-else
                      :icon="['fal', 'square']"
                      class="mr-2 text-theme-500"
                    />
                  </template>

                  {{ $display_name(item.display_name) }}

                  <button
                    v-if="
                      selected_option.id === item.id &&
                      (!divided || selected_divider === box.divider)
                    "
                    class="ml-auto px-2 text-sm font-bold uppercase text-green-500 hover:text-green-600"
                    @click.stop="changeManageExcludes(false)"
                  >
                    {{ $t("done") }}
                  </button>
                </div>
              </li>
            </ul>
          </nav>

          <font-awesome-icon
            v-if="selected_box.id === box.id"
            :icon="['fas', 'play']"
            class="absolute -right-[2px] top-1/2 -translate-y-1/2 transform text-theme-400"
          />
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

/**
 * SingleExcludes Component - One-to-One Exclude Relationship Management
 *
 * Handles creation and deletion of simple bidirectional exclude relationships
 * between product options (e.g., A4 ↔ 135gsm paper weight).
 *
 * Key Features:
 * - Props-first architecture with VueX fallback for backward compatibility
 * - Bidirectional exclude creation and deletion
 * - Divided mode support for calculation grouping
 * - Visual indicators (crown, checkmarks, red styling)
 * - Storage format: `option.excludes = [[excludedOptionId]]`
 *
 * @component SingleExcludes
 * @since 1.0.0
 */
export default {
  name: "SingleExcludes",

  // ============================================================================
  // COMPONENT PROPS - Parent component data integration
  // ============================================================================

  props: {
    /**
     * Current exclude management mode
     * Controls which exclude interface is active in parent ManageExcludes
     *
     * VALUES:
     * - "single" → This component is active
     * - "multiple" → MultipleExcludes component is active
     * - false → ExcludesOverview is active (no editing mode)
     *
     * USAGE: Parent uses this to coordinate between exclude editing modes
     */
    manageExcludes: {
      type: [String, Boolean],
      required: true,
    },

    /**
     * Mode switching function from parent ManageExcludes
     * Called when user wants to change exclude editing mode or return to overview
     *
     * FUNCTION SIGNATURE: (mode: string | false) => void
     *
     * EXAMPLES:
     * - changeManageExcludes(false) → Return to ExcludesOverview
     * - changeManageExcludes("multiple") → Switch to MultipleExcludes mode
     *
     * CRITICAL: This function handles state cleanup and UI transitions
     */
    changeManageExcludes: {
      type: Function,
      required: true,
    },

    /**
     * Divider mode configuration for product structure
     * When true, products are organized by calculation dividers (Format, Material, etc.)
     * When false, all options are shown in a flat structure
     *
     * AFFECTS:
     * - Visual grouping and borders in template
     * - Exclude relationship scope (divided excludes only affect same divider)
     * - Option filtering and display logic
     *
     * LEGACY COMPATIBILITY: Falls back to VueX selected_category.divided if not provided
     */
    divided: {
      type: Boolean,
      required: false,
      default: false,
    },

    /**
     * PROPS-FIRST DATA: Boxes and options from parent ManageExcludes
     * Array of box objects containing options with exclude relationships
     *
     * STRUCTURE:
     * ```javascript
     * [
     *   {
     *     id: "box_id",
     *     display_name: [{iso: "en", display_name: "Paper Type"}],
     *     divider: "Material", // Only in divided mode
     *     ops: [
     *       {
     *         id: "option_id",
     *         display_name: [{iso: "en", display_name: "135gsm"}],
     *         excludes: [[excluded_option_id]] // Single exclude format
     *       }
     *     ]
     *   }
     * ]
     * ```
     *
     * DUAL-STORE PATTERN: Props take priority, falls back to VueX for backward compatibility
     */
    selectedBoops: {
      type: Array,
      required: false,
      default: () => [],
    },

    /**
     * PROPS-FIRST DATA: Currently selected box context from parent
     * The box that contains the option being configured for excludes
     *
     * USAGE:
     * - Determines which box's options can be excluded
     * - Used in get_box_index() for array operations
     * - Context for divider-specific exclude relationships
     *
     * FALLBACK: Uses VueX selected_box if prop not provided
     */
    selectedBox: {
      type: Object,
      required: false,
      default: () => ({}),
    },

    /**
     * PROPS-FIRST DATA: Currently selected option being configured
     * The source option that will exclude other options (bidirectional)
     *
     * VISUAL INDICATOR: Shows crown icon (👑) in UI to identify source option
     *
     * CRITICAL ROLE:
     * - Source of exclude relationships (option.excludes array modified)
     * - Determines which options can be targeted for exclusion
     * - Used in bidirectional exclude creation (A excludes B, B excludes A)
     *
     * FALLBACK: Uses VueX selected_option if prop not provided
     */
    selectedOption: {
      type: Object,
      required: false,
      default: () => ({}),
    },

    /**
     * PROPS-FIRST DATA: Current divider context in divided mode
     * Only options within same divider can exclude each other
     *
     * EXAMPLES: "Format", "Material", "Weight", "PrintingColors"
     *
     * SCOPE CONTROL:
     * - Filters which options are shown for exclude selection
     * - Ensures excludes don't cross divider boundaries
     * - Used in get_box_index() and get_option_index() lookups
     *
     * FALLBACK: Uses VueX selected_divider if prop not provided
     */
    selectedDivider: {
      type: String,
      required: false,
      default: "",
    },
  },
  // ============================================================================
  // COMPONENT DATA - Local reactive state management
  // ============================================================================

  data() {
    return {
      /**
       * LOCAL COPY: Boxes and options for exclude management
       *
       * INITIALIZATION: Set from props or VueX in mounted() lifecycle
       * MUTATION SAFE: Local copy allows safe array manipulation without affecting parent
       *
       * CRITICAL FOR EXCLUDE OPERATIONS:
       * - add_single_exclude() modifies this.boops[box_index].ops[option_index].excludes
       * - delete_single_exclude() removes from this.boops exclude arrays
       * - Maintains reactivity for UI updates during exclude modifications
       *
       * ⚠️  ARRAY FORMAT: Must be array, not object (legacy comment preserved)
       */
      boops: [],

      /**
       * UI STATE: Currently selected excludes for visual feedback
       * Array of option IDs that are selected for exclude relationship creation
       *
       * USAGE:
       * - Visual highlighting of excluded options (red styling)
       * - Temporary storage before exclude relationship is finalized
       * - Currently not actively used but maintained for potential UI enhancements
       */
      selectedExcludes: [],

      /**
       * LEGACY STATE: Generic object reference
       *
       * ⚠️  UNCLEAR PURPOSE: This may be leftover from older implementation
       * TODO: Investigate if this is still needed or can be removed
       */
      object: null,
    };
  },
  // ============================================================================
  // COMPUTED PROPERTIES - Reactive data resolution and state management
  // ============================================================================

  computed: {
    /**
     * LEGACY VUEX STATE MAPPING - Backward compatibility layer
     *
     * Maps VueX product wizard state to component properties for components
     * that haven't been migrated to the new props-first architecture.
     *
     * ⚠️  DUAL COMPATIBILITY:
     * - Modern components pass data via props
     * - Legacy components rely on these VueX mappings
     * - This component supports both patterns simultaneously
     *
     * STATE SOURCES: product_wizard store module (global wizard state)
     */
    ...mapState({
      /** VueX: Global boxes and options array */
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,

      /** VueX: Global selected box object */
      vuex_selected_box: (state) => state.product_wizard.selected_box,

      /** VueX: Global selected option object */
      vuex_selected_option: (state) => state.product_wizard.selected_option,

      /** VueX: Global selected divider string */
      vuex_selected_divider: (state) => state.product_wizard.selected_divider,
    }),

    /**
     * DUAL-STORE DATA ACCESS PATTERN - Props-first with VueX fallback
     *
     * These computed properties implement the modern data access pattern:
     * 1. Check if props contain valid data (props-first)
     * 2. Fall back to VueX state if props unavailable (backward compatibility)
     * 3. Provide empty defaults if neither source available (error resistance)
     *
     * PRIORITY HIERARCHY: Props → VueX → Empty Defaults
     */

    /**
     * RESOLVED BOXES AND OPTIONS - Primary data source for template
     *
     * LOGIC:
     * - Props priority: Use selectedBoops if array has items
     * - VueX fallback: Use vuex_selected_boops if props empty
     * - Safe default: Empty array if both sources unavailable
     *
     * TEMPLATE USAGE: v-for loops over this array to render boxes and options
     * CRITICAL: Changes to this computed trigger template re-renders
     */
    selected_boops() {
      return this.selectedBoops && this.selectedBoops.length > 0
        ? this.selectedBoops
        : this.vuex_selected_boops || [];
    },

    /**
     * RESOLVED SELECTED BOX - Context for exclude operations
     *
     * LOGIC:
     * - Props priority: Use selectedBox if object has keys
     * - VueX fallback: Use vuex_selected_box if props empty
     * - Safe default: Empty object if both sources unavailable
     *
     * USAGE: get_box_index() uses this.selected_box.id for array lookups
     */
    selected_box() {
      return Object.keys(this.selectedBox || {}).length > 0
        ? this.selectedBox
        : this.vuex_selected_box || {};
    },

    /**
     * RESOLVED SELECTED OPTION - Source option for exclude relationships
     *
     * CRITICAL ROLE:
     * - Visual identification: Crown icon (👑) shows this option in UI
     * - Exclude source: This option's excludes array gets modified
     * - Bidirectional target: Other options exclude this option back
     *
     * USAGE: add_single_exclude() reads this.selected_option.id for relationships
     */
    selected_option() {
      return Object.keys(this.selectedOption || {}).length > 0
        ? this.selectedOption
        : this.vuex_selected_option || {};
    },

    /**
     * RESOLVED SELECTED DIVIDER - Scope control for divided mode
     *
     * LOGIC:
     * - Props priority: Use selectedDivider if string provided
     * - VueX fallback: Use vuex_selected_divider if props empty
     * - Safe default: Empty string if both sources unavailable
     *
     * SCOPE FILTERING:
     * - Only options within same divider shown for exclude selection
     * - Empty string means non-divided mode (all options available)
     */
    selected_divider() {
      return this.selectedDivider || this.vuex_selected_divider || "";
    },
  },
  // ============================================================================
  // WATCHERS - Reactive data synchronization and state management
  // ============================================================================

  watch: {
    /**
     * BOXES AND OPTIONS SYNCHRONIZATION - Props to local state sync
     *
     * CRITICAL TIMING:
     * - deep: true → Watches nested array/object changes
     * - immediate: true → Executes on component creation (not just changes)
     *
     * DEEP CLONING RATIONALE:
     * - this.boops needs to be mutable for exclude array manipulation
     * - _.cloneDeep() prevents parent data corruption during exclude operations
     * - Lodash deep clone handles complex nested option structures safely
     *
     * EXECUTION FLOW:
     * 1. Parent changes selectedBoops prop → This watcher triggers
     * 2. Local this.boops updated with fresh deep copy
     * 3. Template re-renders with new data
     * 4. Exclude operations modify local copy without affecting parent
     *
     * ⚠️  PERFORMANCE CONSIDERATION: Deep cloning large arrays can be expensive
     */
    selected_boops: {
      deep: true,
      immediate: true,
      handler(newVal) {
        if (newVal && newVal.length > 0) {
          this.boops = _.cloneDeep(newVal);
        }
      },
    },

    /**
     * LOCAL BOOPS CHANGE DETECTION - Debug and reactivity helper
     *
     * PURPOSE:
     * - Ensures template reactivity when exclude arrays are modified
     * - Can be used for debugging exclude operation side effects
     * - Currently passive (just returns value) but maintains watcher
     *
     * TIMING: Triggered when add_single_exclude() or delete_single_exclude() modify arrays
     *
     * POTENTIAL ENHANCEMENTS:
     * - Could emit events to parent about exclude changes
     * - Could perform validation on exclude array modifications
     * - Could trigger UI state updates (loading, success indicators)
     */
    boops: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },

    /**
     * SELECTED BOX REACTIVITY - Context change handling
     *
     * Maintains component reactivity when parent changes the selected box.
     * Currently passive but preserves watcher structure for future enhancements.
     *
     * POTENTIAL USE CASES:
     * - Clear local state when box context changes
     * - Validate that selected option belongs to selected box
     * - Reset exclude selection state
     */
    selected_box: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },

    /**
     * SELECTED OPTION REACTIVITY - Source option change handling
     *
     * Maintains component reactivity when parent changes the source option.
     * The selected option is critical as it's the source of exclude relationships.
     *
     * POTENTIAL ENHANCEMENTS:
     * - Validate option belongs to current box/divider
     * - Clear any in-progress exclude selections
     * - Update UI state to reflect new source option
     */
    selected_option: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },

    /**
     * SELECTED DIVIDER REACTIVITY - Scope change handling
     *
     * Maintains component reactivity when divider context changes.
     * Divider changes affect which options are available for exclusion.
     *
     * POTENTIAL ENHANCEMENTS:
     * - Filter boops array to only show relevant divider options
     * - Clear exclude selections that cross divider boundaries
     * - Update template rendering for new divider scope
     */
    selected_divider: {
      handler(newVal) {
        return newVal;
      },
    },
  },

  // ============================================================================
  // COMPONENT LIFECYCLE - Initialization and setup
  // ============================================================================

  /**
   * COMPONENT INITIALIZATION - Data setup and debugging
   *
   * CRITICAL INITIALIZATION TASKS:
   * 1. Deep clone selectedBoops to local boops array (mutable copy)
   * 2. Debug logging for development troubleshooting
   * 3. Validate data structure integrity
   *
   * DUAL DATA SOURCE HANDLING:
   * - Priority 1: this.selectedBoops (props-first approach)
   * - Priority 2: this.selected_boops (computed with VueX fallback)
   * - Deep cloning prevents parent data corruption during exclude operations
   *
   * DEBUG LOGGING PURPOSE:
   * - Verify data flow from parent ManageExcludes component
   * - Inspect box/option structure for template rendering
   * - Validate divider configuration in divided mode
   * - Establish props vs VueX data source priorities
   */
  mounted() {
    // PROPS-FIRST INITIALIZATION: Use selectedBoops prop if available
    if (this.selectedBoops && this.selectedBoops.length > 0) {
      this.boops = _.cloneDeep(this.selectedBoops);
    }
  },
  methods: {
    // ========================================================================
    // LEGACY VUEX MUTATIONS - Global state management compatibility
    // ========================================================================

    /**
     * VueX mutation mappings for backward compatibility with legacy wizard
     *
     * These mutations update the global product wizard state and are used
     * when parent component doesn't support modern data delegation patterns.
     *
     * MUTATION PURPOSES:
     * - vuex_set_selected_boops: Update global boxes/options array
     * - vuex_set_selected_box: Update global selected box context
     * - vuex_set_selected_option: Update global selected option context
     * - vuex_set_selected_divider: Update global divider scope
     * - set_selected_boop_excludes_reverse: Legacy exclude relationship creation
     * - set_selected_boop_excludes_reverse_delete: Legacy exclude relationship removal
     *
     * ⚠️  MODERN COMPONENTS: Should use parent delegation instead of direct VueX
     */
    ...mapMutations({
      vuex_set_selected_boops: "product_wizard/set_selected_boops",
      vuex_set_selected_box: "product_wizard/set_selected_box",
      vuex_set_selected_option: "product_wizard/set_selected_option",
      vuex_set_selected_divider: "product_wizard/set_selected_divider",
      set_selected_boop_excludes_reverse:
        "product_wizard/set_selected_boop_single_excludes_reverse",
      set_selected_boop_excludes_reverse_delete:
        "product_wizard/set_selected_boop_single_excludes_reverse_delete",
    }),

    // ========================================================================
    // PARENT DELEGATION METHODS - Modern component communication
    // ========================================================================

    /**
     * DELEGATE BOOPS UPDATE TO PARENT - Modern data flow pattern
     *
     * DELEGATION LOGIC:
     * 1. Check if parent component has modern update method
     * 2. Delegate to parent if available (preferred)
     * 3. Fall back to VueX mutation for legacy compatibility
     *
     * PARENT METHOD SIGNATURE: set_selected_boops(boops: Array) => void
     *
     * USAGE: Called after exclude operations to sync changes up to parent
     * MODERN FLOW: Child → Parent → Store (controlled data flow)
     * LEGACY FLOW: Child → Store (direct mutation)
     */
    set_selected_boops(boops) {
      if (this.$parent?.set_selected_boops) {
        this.$parent.set_selected_boops(boops);
      } else {
        this.vuex_set_selected_boops(boops);
      }
    },
    checkState(divider, option_id) {
      if (option_id === this.selected_option.id) {
        return false; // Don't show X on the selected option itself
      }

      // Check if selected option excludes this option (A excludes B)
      const selectedExcludesThis =
        this.selected_option.excludes &&
        this.selected_option.excludes.some(
          (x) => Array.isArray(x) && x.length > 0 && x.length < 2 && x.includes(option_id),
        );

      // Check if this option excludes the selected option (B excludes A)
      const thisExcludesSelected =
        this.boops &&
        this.boops.some(
          (box) =>
            box.ops &&
            box.ops.some(
              (option) =>
                option.id === option_id &&
                option.excludes &&
                option.excludes.some(
                  (x) =>
                    Array.isArray(x) &&
                    x.length > 0 &&
                    x.length < 2 &&
                    x.includes(this.selected_option.id),
                ),
            ),
        );

      const hasExclude = thisExcludesSelected;
      const dividerMatch = !divider || this.selected_divider === divider;

      return hasExclude && dividerMatch;
    },

    checkIfEntireBox(divider, box_id) {
      const box = this.boops.find((box) => {
        if (divider) {
          return box.id === box_id && box.divider === divider;
        } else {
          return box.id === box_id;
        }
      });
      return box.ops.every((option) => {
        return option.excludes.some(
          (ex) => Array.isArray(ex) && ex.length === 1 && ex[0] === this.selected_option.id,
        );
      });
    },

    set_single_excludes(box_id, option_id, divider = null) {
      if (!this.checkState(divider, option_id)) {
        this.set_single_exclude(option_id, box_id, divider);
      } else {
        this.delete_single_exclude(option_id, box_id, divider);
      }

      this.set_selected_boops(this.boops);
    },

    Manage_single_entire_excludes(box_id, divider = null) {
      const box_index = this.boops.findIndex((box) => {
        if (divider) {
          return box.id === box_id && box.divider === divider;
        } else {
          return box.id === box_id;
        }
      });
      if (this.checkIfEntireBox(divider, box_id)) {
        this.remove_single_entire_excludes(box_index, divider);
      } else {
        this.set_single_entire_excludes(box_index, divider);
      }
      this.set_selected_boops(this.boops);
    },

    set_single_entire_excludes(box_index, divider = null) {
      this.boops[box_index].ops.forEach((option, index) => {
        if (!option.excludes.some((ex) => Array.isArray(ex) && ex[0] === this.selected_option.id)) {
          this.boops[box_index].ops[index].excludes.push([this.selected_option.id]);
          const reverse_box_index = this.get_box_index(this.selected_box?.id, divider);
          const reverse_option_index = this.get_option_index(
            reverse_box_index,
            this.selected_option?.id,
            divider,
          );
          this.boops[reverse_box_index].ops[reverse_option_index].excludes.push([
            this.boops[box_index].ops[index].id,
          ]);
          this.selected_option.excludes.push([this.boops[box_index].ops[index].id]);
        }
      });
    },

    remove_single_entire_excludes(box_index, divider = null) {
      this.boops[box_index].ops.forEach((option, index) => {
        const excludeIndex = option.excludes.findIndex(
          (ex) => Array.isArray(ex) && ex.length === 1 && ex[0] === this.selected_option.id,
        );
        if (excludeIndex >= 0) {
          this.boops[box_index].ops[index].excludes.splice(excludeIndex, 1);
        }

        const indexReverse = this.selected_option.excludes.findIndex(
          (x) =>
            Array.isArray(x) && x.length === 1 && x.includes(this.boops[box_index].ops[index].id),
        );
        console.log(indexReverse);
        if (indexReverse >= 0) {
          const reverse_box_index = this.get_box_index(this.selected_box?.id, divider);
          const reverse_option_index = this.get_option_index(
            reverse_box_index,
            this.selected_option?.id,
            divider,
          );
          this.boops[reverse_box_index].ops[reverse_option_index].excludes.splice(indexReverse, 1);
          this.selected_option.excludes.splice(indexReverse, 1);
        }
        console.log(this.selected_option);
      });
    },

    set_single_exclude(o_id, b_id, divider = null) {
      // If no option is currently selected, select this option instead of creating exclude
      if (!this.selected_box?.id || !this.selected_option?.id) {
        // Find the box and option to set as selected
        const box = this.boops.find((box) => box.id === b_id);
        const option = box?.ops?.find((opt) => opt.id === o_id);

        if (box && option) {
          // Set selected box and option (this should trigger parent component updates)
          this.vuex_set_selected_box(box);
          this.vuex_set_selected_option(option);
          if (divider) {
            this.vuex_set_selected_divider(divider);
          }
        }
        return;
      }

      const box_index = this.get_box_index(this.selected_box?.id, divider);
      const option_index = this.get_option_index(box_index, this.selected_option?.id, divider);

      const reverse_box_index = this.get_box_index(b_id, divider);
      const reverse_option_index = this.get_option_index(reverse_box_index, o_id, divider);

      if (box_index < 0 || option_index < 0 || reverse_box_index < 0 || reverse_option_index < 0) {
        return;
      }

      // Ensure excludes arrays are initialized
      if (!Array.isArray(this.boops[box_index].ops[option_index].excludes)) {
        this.boops[box_index].ops[option_index].excludes = [];
      }
      if (!Array.isArray(this.boops[reverse_box_index].ops[reverse_option_index].excludes)) {
        this.boops[reverse_box_index].ops[reverse_option_index].excludes = [];
      }

      // Only add if not already present
      if (
        !this.boops[box_index].ops[option_index].excludes.some(
          (ex) => Array.isArray(ex) && ex[0] === o_id,
        )
      ) {
        // set main exclude
        this.boops[box_index].ops[option_index].excludes.push([o_id]);
        this.selected_option.excludes = this.boops[box_index].ops[option_index].excludes;

        // set reverse exclude
        this.boops[reverse_box_index].ops[reverse_option_index].excludes.push([
          this.selected_option.id,
        ]);
      }
    },
    delete_single_exclude(o_id, b_id, divider) {
      const box_index = this.get_box_index(this.selected_box.id, divider);
      const option_index = this.get_option_index(box_index, this.selected_option.id, divider);

      const reverse_box_index = this.get_box_index(b_id, divider);
      const reverse_option_index = this.get_option_index(reverse_box_index, o_id, divider);

      // delete main exclude
      const i = this.boops[box_index].ops[option_index].excludes.findIndex((ex) => ex[0] === o_id);
      this.boops[box_index].ops[option_index].excludes.splice(i, 1);
      this.selected_option.excludes = this.boops[box_index].ops[option_index].excludes;

      // delete reverse exclude
      const ix = this.boops[reverse_box_index].ops[reverse_option_index].excludes.findIndex(
        (ex) => ex[0] === this.selected_option.id,
      );
      this.boops[reverse_box_index].ops[reverse_option_index].excludes.splice(ix, 1);
    },

    get_box_index(box_id, divider = null) {
      if (divider) {
        return this.boops.findIndex((box) => box.id === box_id && box.divider === divider);
      }
      return this.boops.findIndex((box) => box.id === box_id);
    },
    get_option_index(box_index, option_id, divider = null) {
      // Defensive checks
      if (!this.boops || !Array.isArray(this.boops)) {
        return -1;
      }

      if (box_index < 0 || box_index >= this.boops.length) {
        return -1;
      }

      const box = this.boops[box_index];
      if (!box || !box.ops || !Array.isArray(box.ops)) {
        return -1;
      }

      if (divider) {
        return box.ops.findIndex((opt) => opt.id === option_id && box.divider === divider);
      }
      return box.ops.findIndex((opt) => opt.id === option_id);
    },
  },
};
</script>

<style></style>
