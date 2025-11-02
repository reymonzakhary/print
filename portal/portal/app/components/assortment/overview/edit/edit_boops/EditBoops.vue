<template>
  <div class="relative h-full" :class="{ 'wizard-mode': wizardMode }">
    <!-- accordion -->
    <section
      v-show="!searchBoop && !manageExcludes && !sortBoops"
      :class="['mx-auto flex w-full flex-col', wizardMode ? 'p-4' : 'p-2']"
    >
      <transition name="slide">
        <div
          v-show="!sortOps"
          class="my-2 flex w-full items-center justify-between italic text-gray-500 dark:text-gray-300"
        >
          {{ $t("review boxes and options") }}
          <button
            v-if="localSelectedBoops && localSelectedBoops.length > 0 && !divided"
            class="ml-4 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="sortBoops = true"
          >
            <font-awesome-icon :icon="['fal', 'arrows-repeat']" />
            {{ $t("Reorder boxes") }}
          </button>
          <div class="flex items-center">
            <VTooltip>
              <button
                v-if="editable"
                class="ml-4 flex items-center text-left text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                @click="
                  ((searchUrl = '/finder/boxes/search?search='),
                  (searchBoop = true),
                  (searchType = 'box'),
                  (addType = 'new'))
                "
              >
                <font-awesome-icon :icon="['fal', 'box-open']" />
                <font-awesome-icon :icon="['fal', 'plus']" />
                <p>{{ $t("Create new box") }}</p>
              </button>

              <template #popper>
                <div class="max-w-xs p-3">
                  {{
                    // prettier-ignore
                    $t('Create a new box by searching through standardized products and materials. This will search the global database for matching items and create a new box configuration based on standardization data.')
                  }}
                </div>
              </template>
            </VTooltip>

            <VTooltip>
              <UIButton
                v-if="editable"
                variant="theme"
                class="ml-4 px-3 py-1 text-left !text-base"
                @click="
                  ((searchUrl = '/finder/boxes/search?search='),
                  (searchBoop = true),
                  (searchType = 'box'),
                  (addType = 'exist'))
                "
              >
                <font-awesome-icon :icon="['fal', 'box-open']" />
                <font-awesome-icon :icon="['fal', 'plus']" />
                <span class="ml-1">{{ $t("Add existing box") }}</span>
              </UIButton>

              <template #popper>
                <div class="max-w-xs p-3">
                  {{
                    // prettier-ignore
                    $t('Add an existing box from your previously created boxes. This allows you to reuse box configurations that you have already set up, saving time by not having to recreate common box types.')
                  }}
                </div>
              </template>
            </VTooltip>
          </div>
        </div>
      </transition>

      <PrintProductEditBoopsAccordion
        v-if="localSelectedBoops && localSelectedBoops.length > 0"
        :data="localSelectedBoops"
        :editable="editable"
        :divided="divided"
        :wizard-mode="wizardMode"
        class="h-full w-full overflow-y-auto"
        @remove-box="remove"
        @edit-box="editBox($event)"
      >
        <template #default="{ box, index }">
          <section
            v-show="!sortOps"
            :class="{ '-m-6 mb-0 rounded bg-gray-50 p-4 dark:bg-gray-750': wizardMode }"
          >
            <!-- BOOPS MENU -->
            <div class="mb-2 flex flex-wrap items-center justify-between">
              <p class="italic text-gray-500 dark:text-gray-400">
                {{ $t("review") }}
                {{ $display_name(box.display_name) }}
                {{ $t("options") }}
              </p>
              <button
                class="text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                @click="sortOps = true"
              >
                <font-awesome-icon :icon="['fal', 'arrows-repeat']" />
                <!-- <font-awesome-icon :icon="['fal', 'plus']" /> -->
                {{ $t("Reorder options") }}
              </button>
              <div class="flex items-center gap-3">
                <VTooltip>
                  <button
                    v-if="editable"
                    class="text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                    @click="
                      ((searchUrl = '/finder/options/search?search='),
                      (searchBoop = true),
                      (selectedBox = Object.assign({ index: index }, box)),
                      (searchType = 'option'),
                      (addType = 'new'))
                    "
                  >
                    <font-awesome-icon :icon="['fal', 'plus']" />
                    {{ $t("Create new option") }}
                  </button>

                  <template #popper>
                    <div class="max-w-xs p-3">
                      {{
                        // prettier-ignore
                        $t("Create a new option by searching through standardized materials and finishes. This searches the global database to find matching option configurations and creates a new option for this box based on industry standards.")
                      }}
                    </div>
                  </template>
                </VTooltip>

                <VTooltip>
                  <UIButton
                    v-if="editable"
                    variant="theme"
                    class="ml-4 px-3 py-1 !text-base"
                    @click="
                      ((searchUrl = '/finder/options/search?search='),
                      (searchBoop = true),
                      (selectedBox = Object.assign({ index: index }, box)),
                      (searchType = 'option'),
                      (addType = 'exist'))
                    "
                  >
                    <font-awesome-icon :icon="['fal', 'plus']" />
                    {{ $t("Add existing option") }}
                  </UIButton>

                  <template #popper>
                    <div class="max-w-xs p-3">
                      {{
                        $t(
                          "Add an existing option from your previously created options library. This lets you reuse option configurations like materials, finishes, or specifications that you have already defined for other boxes.",
                        )
                      }}
                    </div>
                  </template>
                </VTooltip>
              </div>
            </div>

            <div class="h-full overflow-y-auto" style="max-height: calc(100vh - 28rem)">
              <!-- OPTIONS MULTISELECT MENU -->
              <transition name="slide">
                <div
                  v-if="active_box === index && selectedOptionsList.length > 0"
                  class="sticky top-0 rounded-t bg-white px-2 py-1 text-xs text-gray-500 shadow"
                >
                  <span class="font-bold">{{ selectedOptionsList.length }}</span>
                  {{ $t("selected") }}
                  <UIButton
                    variant="inverted-danger"
                    class="ml-8 bg-gray-100"
                    @click="removeMultipleBoops(index)"
                  >
                    <font-awesome-icon
                      aria-hidden="true"
                      :icon="['fal', 'layer-minus']"
                      class="mr-1"
                    />
                    {{ $t("Remove selected") }}
                  </UIButton>
                </div>
              </transition>

              <label
                v-if="box.ops && box.ops.length > 0"
                class="flex cursor-pointer items-center p-2 text-sm"
              >
                <input
                  type="checkbox"
                  class="mx-2"
                  :checked="selectedOptionsList.length === box.ops.length"
                  @change="box.ops.forEach((option) => toggle(option.id))"
                />
                {{ $t("Select all") }}
              </label>

              <!-- OPTIONS -->
              <template v-if="box.ops && box.ops.length > 0">
                <div
                  v-for="(option, idx) in box.ops || []"
                  :key="'option_' + option.slug"
                  class="group flex items-center justify-between border-t px-2 py-1 first:border-t-0 hover:bg-gray-200 dark:border-black dark:hover:bg-black"
                >
                  <label
                    class="flex w-1/2 items-center justify-between text-xs font-bold tracking-wide text-gray-700 dark:text-gray-200 md:mx-2"
                    :for="'option_' + option.slug"
                  >
                    <div class="flex-1">
                      <input
                        :id="'option_' + option.slug"
                        type="checkbox"
                        class="mr-2"
                        :checked="selectedOptionsList.includes(option.id)"
                        :aria-label="$t('Select option') + ' ' + $display_name(option.display_name)"
                        @change="toggle(option.id)"
                      />
                      {{ $display_name(option.display_name) }}
                    </div>

                    <small
                      v-tooltip="$t('original name')"
                      class="ml-2 flex-1 text-gray-700 dark:text-gray-300"
                    >
                      {{ option.name }}
                    </small>

                    <font-awesome-icon
                      v-if="
                        option.additional?.calc_ref && option.additional?.calc_ref !== box.calc_ref
                      "
                      v-tooltip="
                        // prettier-ignore
                        $t('this option has a different calculation reference then the box. The calculation will not work for this option.') +
                            ' ' +
                            option.additional.calc_ref
                      "
                      class="max-w-fit flex-1 text-sm text-amber-700 dark:text-amber-300"
                      fixed-with
                      :icon="['fal', calcRef(option.additional?.calc_ref)]"
                      aria-label="$t('Different calculation reference')"
                    />
                    <font-awesome-icon
                      v-if="
                        option.additional?.calc_ref && option.additional?.calc_ref === box.calc_ref
                      "
                      class="max-w-fit flex-1 text-sm text-green-700 dark:text-green-300"
                      fixed-width
                      :icon="['fal', calcRef(option.additional?.calc_ref)]"
                      aria-label="$t('Matching calculation reference')"
                    />
                  </label>

                  <span>
                    <button
                      v-if="editable"
                      class="invisible rounded-full px-2 text-theme-700 hover:bg-theme-100 group-hover:visible dark:text-theme-200 dark:hover:bg-theme-800"
                      aria-label="$t('Edit option') + ' ' + $display_name(option.display_name)"
                      type="button"
                      @click.stop="menuItemClicked('editOption', option)"
                    >
                      <font-awesome-icon aria-hidden="true" :icon="['fal', 'pencil']" />
                    </button>
                    <button
                      v-if="editable"
                      class="invisible rounded-full px-2 text-red-700 hover:bg-red-100 group-hover:visible dark:text-red-300 dark:hover:bg-red-800"
                      aria-label="$t('Remove option') + ' ' + $display_name(option.display_name)"
                      type="button"
                      @click.stop="remove(option, 'option', index, idx)"
                    >
                      <font-awesome-icon aria-hidden="true" :icon="['fal', 'trash-can']" />
                    </button>
                  </span>
                </div>
              </template>
            </div>
          </section>

          <!-- SORT OPTION >> -->
          <div
            v-if="sortOps"
            class="h-full overflow-y-auto"
            style="max-height: calc(100vh - 28rem)"
          >
            <p class="mb-2 flex w-full justify-between italic text-gray-600">
              {{ $t("Sort options below") }}
              <button
                class="ml-4 text-green-500 hover:text-green-600 dark:hover:text-green-400"
                @click="(updateBoopsData(localSelectedBoops), (sortOps = false))"
              >
                <font-awesome-icon :icon="['fal', 'check']" />
                <!-- <font-awesome-icon :icon="['fal', 'plus']" /> -->
                {{ $t("Save order") }}
              </button>
            </p>

            <draggable :list="box.ops" item-key="optionList">
              <template #item="{ element: option }">
                <div
                  class="group flex items-center justify-between border-t px-2 py-1 first:border-t-0 hover:bg-gray-200"
                >
                  <label>
                    {{ $display_name(option.display_name) }}
                  </label>
                  <font-awesome-icon :icon="['fal', 'grip-lines']" />
                </div>
              </template>
            </draggable>
          </div>
        </template>
      </PrintProductEditBoopsAccordion>

      <div v-else class="mb-2 mt-20 hidden h-full w-full justify-center font-bold lg:flex">
        <div class="flex h-full w-full flex-col flex-wrap items-center justify-center text-center">
          <p class="text-xl font-bold text-gray-400">
            {{ $t("no box added yet") }}
          </p>

          <div class="my-8 flex items-start justify-center">
            <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
            <font-awesome-icon :icon="['fad', 'box-open']" class="fa-5x my-4 text-gray-400" />
            <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
          </div>

          <button
            v-if="editable"
            class="ml-4 rounded-full border border-theme-500 px-2 py-1 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="
              ((searchUrl = '/finder/boxes/search?search='),
              (searchBoop = true),
              (searchType = 'box'),
              addType === 'new')
            "
          >
            <font-awesome-icon :icon="['fal', 'box-open']" />
            <font-awesome-icon :icon="['fal', 'plus']" />
            {{ $t("Add your first box") }}
          </button>
        </div>
      </div>
    </section>

    <OptionsEditPanel
      v-if="component === 'OptionsEditPanel'"
      :show-runs-panel="true"
      @on-close="component = false"
      @on-updated="onBoxOrOptionUpdated('option', $event)"
    />
    <BoxesEditPanel
      v-if="component === 'BoxesEditPanel'"
      @on-close="component = false"
      @on-updated="onBoxOrOptionUpdated('box', $event)"
    />

    <!-- SORT BOXES -->
    <section v-if="sortBoops" :class="['mx-auto flex w-full flex-col p-2']">
      <p class="my-2 flex w-full justify-between italic text-gray-600">
        {{ $t("Sort boxes below") }}
        <button
          class="ml-4 text-green-500 hover:text-green-600 dark:hover:text-green-400"
          @click="finishSorting"
        >
          <font-awesome-icon :icon="['fal', 'check']" />
          {{ $t("Save order") }}
        </button>
      </p>

      <!-- Groups with clear separation -->
      <div
        v-for="(collection, groupKey) in localGroupedBoops"
        :key="groupKey"
        :class="
          groupKey
            ? `mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-800`
            : ''
        "
      >
        <!-- Group Header -->
        <div v-if="groupKey" class="mb-4 flex items-center justify-between">
          <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
            <font-awesome-icon :icon="['fal', 'layer-group']" class="mr-2" />
            {{ groupKey }}
          </h4>
          <span class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-600">
            {{ collection.length }} {{ collection.length === 1 ? "box" : "boxes" }}
          </span>
        </div>

        <!-- Draggable area with minimum height -->
        <draggable
          v-model="localGroupedBoops[groupKey]"
          item-key="id"
          :animation="200"
          :group="`group-${groupKey}`"
          :disabled="false"
          ghost-class="ghost"
          class="min-h-[120px] space-y-2 rounded border-2 border-dashed border-gray-300 p-3 dark:border-gray-600"
          @start="drag = true"
          @end="onDragEnd"
        >
          <template #item="{ element: box }">
            <div
              class="group mb-1 flex cursor-pointer justify-between rounded border-2 bg-white p-3 transition hover:shadow-lg dark:bg-gray-700"
            >
              {{ box.name }}
            </div>
          </template>
        </draggable>
      </div>
    </section>

    <PrintProductEditBoopsRemoveModal
      v-if="removeItem"
      :item="removeItem"
      :box_index="removeBoxIndex"
      :option_index="removeOptionIndex"
      :type="removeType"
      @delete-item="removeBoop"
    />

    <transition name="fade">
      <section v-if="searchBoop" class="relative">
        <div
          :class="[
            '-top-4 z-30 mx-auto flex w-full items-end px-2 py-2',
            { 'sticky bg-white dark:bg-gray-700': wizardMode },
          ]"
        >
          <div class="text-lg font-semibold">
            <span v-if="searchType === 'box'" class="flex items-center space-x-2">
              <UIPrindustryBox v-if="addType === 'new'" class="size-6" line="text-prindustry-500" />
              <UITenantLogo v-if="addType === 'exist'" class="w-6" />
              <font-awesome-icon :icon="['fal', 'box-open']" />
              <h3>{{ addType === "new" ? $t("Create new box") : $t("Add box") }}</h3>
            </span>
            <span v-if="searchType === 'option'">
              <font-awesome-icon :icon="['fal', 'plus']" />
              {{ addType === "new" ? $t("Create new option") : $t("Add option") }} (
              <font-awesome-icon :icon="['fal', 'box-open']" />
              {{ $display_name(selectedBox.display_name) }} )
            </span>
          </div>
          <button
            class="ml-auto text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="searchBoop = false"
          >
            <font-awesome-icon :icon="['fad', 'circle-xmark']" />
            {{ $t("Cancel search") }}
          </button>
        </div>

        <AddProductSearch
          v-if="addType == 'new'"
          :search-url="searchUrl"
          :type="searchType"
          :wizard-mode="wizardMode"
          :sticky-search="wizardMode ? true : false"
          @add-new="addNewBoop"
          @update-name="set_name"
          @update-display-name="set_display_name"
        />
        <AddExistingProduct
          v-if="addType == 'exist'"
          :search-url="searchUrl"
          :type="searchType"
          :wizard-mode="wizardMode"
          :sticky-search="wizardMode ? true : false"
          @add-new="addExistingBoop"
          @toggle-to-create="toggleToCreate"
          @update-name="set_name"
          @update-display-name="set_display_name"
        />
      </section>
    </transition>
  </div>
</template>

<script>
/**
 * EditBoops Component (Legacy + New Wizard Flow Support)
 * ====================================================
 *
 * OVERVIEW:
 * This component manages box and option editing for both the legacy category
 * management system and the new product wizard flow. It provides a unified
 * interface for CRUD operations on boxes and options.
 *
 * LEGACY FUNCTIONALITY:
 * - VueX store integration for category and product state
 * - Box and option management via traditional API calls
 * - Direct category editing from assortment overview
 *
 * NEW WIZARD FLOW FEATURES:
 * - Pinia productWizardStore integration
 * - Wizard step data persistence
 * - Dynamic data source detection (VueX vs Pinia)
 * - Seamless integration with wizard flow steps
 *
 * DUAL COMPATIBILITY:
 * - Detects data source (wizard vs legacy) automatically
 * - Uses appropriate store (Pinia vs VueX) based on context
 * - Maintains state consistency across both systems
 * - Preserves existing API and component interfaces
 *
 * WIZARD INTEGRATION:
 * - Reads category/boops data from wizard store when available
 * - Updates wizard step data when changes are made
 * - Supports wizard state persistence and recovery
 * - Works seamlessly within wizard flow steps
 *
 * @see productWizard.js - Pinia store for wizard flow
 * @see PrintProductEditBoops.vue - Wizard step component that uses this
 */

import { mapState, mapMutations } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";
import { useBoxesAndOptions } from "@/stores/BoxesAndOption";
import mixin from "~/components/assortment/add_products/customProductMixin.js";
import _ from "lodash";
import AddExistingProduct from "~/components/assortment/add_products/custom_product/AddExistingProduct.vue";
import UITenantLogo from "~/components/global/ui/UITenantLogo.vue";
import wizard from "~/store/wizard";

export default {
  components: { AddExistingProduct },
  mixins: [mixin],
  // prevent routing/navigating
  beforeRouteEnter(_, __, next) {
    if (this.changesMade) {
      this.confirm({
        title: this.$t("unsaved changes"),
        message: this.$t("you have unsaved changes. are you sure you want to leave?"),
        confirmOptions: {
          label: this.$t("leave"),
          variant: "warning",
        },
        cancelOptions: {
          label: this.$t("stay"),
          variant: "secondary",
        },
      })
        .then(() => {
          this.$store.commit("settings/setPreventNavigation", true); // Reset the flag if user confirms
        })
        .catch(() => {
          // do nothing
        });
      this.$store.commit("settings/setPreventNavigation", false); // Reset the flag if user confirms
    }
    next(); // Allow navigation
  },
  props: {
    editable: {
      type: Boolean,
      default: true,
      required: false,
    },
    divided: {
      type: Boolean,
      default: false,
      required: false,
    },
    selectedBoops: {
      type: Array,
      default: () => [],
      required: false,
    },
    selectedCategory: {
      type: Object,
      default: () => ({}),
      required: false,
    },
    wizardMode: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  emits: ["doneOrdering", "ordering", "changesMade", "boopsUpdated"],
  setup() {
    const boopsStore = useBoxesAndOptions();
    const api = useAPI();
    const { locale } = useI18n();
    const { handleError, handleSuccess } = useMessageHandler();
    const { confirm } = useConfirmation();
    const { addToast } = useToastStore();

    return {
      api,
      handleError,
      handleSuccess,
      confirm,
      addToast,
      boopsStore,
      locale,
    };
  },
  data() {
    return {
      localSelectedBoops: [], // Local copy of selectedBoops for editing
      selectedBox: {},
      selectedOptionsList: [],
      removeItem: false,
      removeType: "",
      removeBoxIndex: null,
      removeOptionIndex: null,
      searchBoop: false,
      addType: "",
      searchUrl: "",
      searchType: "",
      manageExcludes: false,
      sortBoops: false,
      sortOps: false,
      component: false,
      drag: false,
      changesMade: false,
      localGroupedBoops: {},
    };
  },
  computed: {
    // Legacy Vuex mappings (for legacy VueX integration when no props provided)
    ...mapState({
      vuex_supplier_id: (state) => state.product_wizard.selected_producer.id,
      vuex_selected_category: (state) => state.product_wizard.selected_category,
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,
      vuex_selected_search_item: (state) => state.product_wizard.selected_search_item,
      vuex_search: (state) => state.product_wizard.search,
      vuex_name: (state) => state.product_wizard.name,
      vuex_display_name: (state) => state.product_wizard.display_name,
      active_box: (state) => state.accordion.active_box,
    }),

    // Simple computed properties that use props first, fallback to VueX
    selected_category() {
      return Object.keys(this.selectedCategory).length > 0
        ? this.selectedCategory
        : this.vuex_selected_category;
    },

    selected_boops() {
      return this.selectedBoops.length > 0 ? this.selectedBoops : this.vuex_selected_boops;
    },

    category_slug() {
      return this.selected_category?.slug || "";
    },

    supplier_id() {
      return this.vuex_supplier_id;
    },

    selected_search_item() {
      return this.vuex_selected_search_item;
    },

    search() {
      return this.vuex_search;
    },

    system_key() {
      return this.vuex_system_key;
    },

    custom_name() {
      return this.vuex_custom_name;
    },
  },
  watch: {
    // Watch for props changes (important for wizard continuation)
    selectedBoops: {
      deep: true,
      immediate: true,
      handler(newVal) {
        if (newVal && newVal.length > 0 && newVal !== this.localSelectedBoops) {
          // Ensure each box has an ops array (defensive check for race conditions)
          const sanitizedBoops = _.cloneDeep(newVal).map((box) => ({
            ...box,
            ops: box.ops || [],
          }));
          this.localSelectedBoops = sanitizedBoops;
          this.updateLocalGroupedBoops();
        }
      },
    },

    selected_boops: {
      deep: true,
      immediate: true,
      handler(newVal) {
        if (this.localSelectedBoops !== newVal && this.localSelectedBoops?.length !== 0) {
          this.changesMade = true;
        } else {
          this.changesMade = false;
        }
        // Ensure each box has an ops array (defensive check for race conditions)
        const sanitizedBoops = _.cloneDeep(newVal).map((box) => ({
          ...box,
          ops: box.ops || [],
        }));
        this.localSelectedBoops = sanitizedBoops;
        this.updateLocalGroupedBoops();
      },
    },
    changesMade(v) {
      if (v) {
        this.$emit("changesMade");
        this.$store.commit("settings/setPreventNavigation", true); // Set the flag in Vuex to prevent route change
      }
    },
    selected_box: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    active_box: {
      deep: true,
      handler(newVal) {
        this.selectedOptionsList = [];
        return newVal;
      },
    },
    selected_option: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    sortBoops(v) {
      if (v) {
        this.updateLocalGroupedBoops();
        this.$emit("ordering");
      } else {
        this.$emit("doneOrdering");
      }
    },
    sortOps(v) {
      if (v) {
        this.$emit("ordering");
      } else {
        this.$emit("doneOrdering");
      }
    },
    searchBoop(v) {
      if (v) {
        this.$emit("ordering");
      } else {
        this.$emit("doneOrdering");
      }
    },
  },
  // prevent page refresh
  beforeUnmount() {
    window.removeEventListener("beforeunload", this.beforeUnload);
  },
  mounted() {
    window.addEventListener("beforeunload", this.beforeUnload);

    // Initialize local data from props or VueX
    this.localSelectedBoops = _.cloneDeep(this.selected_boops);

    // Only load from API if we have no data AND we have a category to load from
    // In wizard continuation mode, data should come from props/wizard store
    if (this.localSelectedBoops.length === 0 && this.selected_category?.slug) {
      this.getBoops();
    }
  },

  methods: {
    /**
     * ============================================
     * HYBRID SYSTEM METHODS (Legacy + New Wizard)
     * ============================================
     *
     * These methods work with both VueX (legacy) and Pinia (wizard) stores.
     * Data source detection and auto-save are handled automatically.
     */

    // Legacy Vuex mappings (for backward compatibility when no props provided)
    ...mapMutations({
      vuex_set_custom_name: "product_wizard/set_custom_name",
      vuex_set_selected_category: "product_wizard/set_selected_category",
      vuex_set_selected_boops: "product_wizard/set_selected_boops",
      set_item: "assortmentsettings/set_item",
      set_runs: "assortmentsettings/set_runs",
      set_flag: "assortmentsettings/set_flag",
    }),

    /**
     * Simple method to update boops data
     * Emits event for parent to handle (wizard or legacy)
     */
    updateBoopsData(boops) {
      this.localSelectedBoops = _.cloneDeep(boops);
      this.$emit("boopsUpdated", boops);

      // Fallback to VueX for legacy compatibility if no parent handler
      if (Object.keys(this.selectedCategory).length === 0) {
        this.vuex_set_selected_boops(boops);
      }
    },
    toggleToCreate() {
      this.addType = "new";
    },
    beforeUnload(e) {
      if (this.changesMade) {
        const confirmationMessage = "Are you sure you want to leave this page?";
        e.preventDefault();
        e.returnValue = confirmationMessage; // Standard
        return confirmationMessage; // Gecko + IE
      }
    },
    updateLocalGroupedBoops() {
      this.localGroupedBoops = this.localSelectedBoops.reduce((acc, obj) => {
        const key = obj.divider && this.divided ? obj.divider : ""; // Handle not divided categories
        if (!acc[key]) {
          acc[key] = [];
        }
        acc[key].push(obj);
        return acc;
      }, {});
    },
    onDragEnd() {
      this.drag = false;
    },
    /**
     * Simple method to finish sorting boxes
     */
    finishSorting() {
      // Flatten the grouped collections back to selected boops
      const flattenedBoops = Object.values(this.localGroupedBoops).flat();
      this.updateBoopsData(flattenedBoops);
      this.sortBoops = false;
    },
    toggle(id) {
      // search in selected array
      if (!this.selectedOptionsList.includes(id)) {
        // add new selected item
        this.selectedOptionsList.push(id);
      } else {
        // remove selected item
        const index = this.selectedOptionsList.findIndex((x) => x === id);
        this.selectedOptionsList.splice(index, 1);
      }
    },
    /**
     * Simple method to load boops data from API if needed
     */
    async getBoops() {
      // Check if we have a valid category with both slug and id
      if (!this.selected_category?.slug || !this.selected_category?.id) {
        return;
      }

      // Check if we already have boops
      if (this.localSelectedBoops.length > 0) {
        return;
      }

      try {
        const response = await this.api.get(`categories/${this.selected_category.slug}`);

        // Handle 404 or error responses
        if (!response || response.status === 404 || !response.data) {
          return;
        }

        // Handle different API response structures
        let boops = [];

        // Check various possible locations for boops data
        if (response.data.boops && Array.isArray(response.data.boops)) {
          // If boops is an array at root level
          boops = response.data.boops[0]?.boops || response.data.boops;
        } else if (response.data.boops) {
          // If boops exists but isn't array
          boops = response.data.boops;
        }

        if (boops.length > 0) {
          this.updateBoopsData(boops);
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    /**
     * Create a new box or option from search results
     */
    async addNewBoop() {
      try {
        const payload = this.buildBoopPayload(true); // true for new
        const response = await this.createBoop(payload);
        if (this.searchType === "option") {
          const showRes = await this.api.get(
            `categories/${this.selected_category.id}/options/${response.data.id}`,
          );
          showRes.message = "Option created successfully.";
          this.handleBoopCreation(showRes);
        } else {
          this.handleBoopCreation(response);
        }
      } catch (error) {
        this.handleError(error);
      }
    },

    /**
     * Add an existing box or option
     */
    async addExistingBoop() {
      try {
        const payload = this.buildBoopPayload(false); // false for existing
        const response = await this.createBoop(payload);
        this.handleBoopCreation(response);
      } catch (error) {
        this.handleError(error);
      }
    },

    /**
     * Build payload for box or option creation
     */
    buildBoopPayload(isNew) {
      let linked = "";
      if (isNew) linked = this.selected_search_item.linked;
      else linked = this.selected_search_item.linked;

      // Base payload
      const payload = {
        name: this.name || this.selected_search_item.name || this.search,
        system_key: this.name || this.selected_search_item.name || this.search,
        display_name: Array.isArray(this.selected_search_item.display_name)
          ? this.selected_search_item.display_name.find((x) => x.iso === this.locale)
          : [
              {
                iso: this.locale,
                display_name:
                  this.display_name || this.selected_search_item.display_name || this.search,
              },
            ],
        linked: linked,
        published: true,
        input_type: "checkbox", // TODO change it to radio as default
        category_id: this.selected_category.id,
      };

      // Add additional data for options or existing items
      if (this.searchType === "option" || !isNew) {
        payload.additional = this.selected_search_item.additional || [];
      }

      return payload;
    },

    onBoxOrOptionUpdated(type, data) {
      const boops = _.cloneDeep(this.localSelectedBoops);
      boops.forEach((box, boxIndex) => {
        if (type === "box") {
          if (box.id === data.id) {
            boops[boxIndex] = {
              ...data,
              ops: box.ops,
            };
          }
        } else {
          box.ops.forEach((option, optionIndex) => {
            if (option.id === data.id) {
              boops[boxIndex].ops[optionIndex] = {
                ...data,
                excludes: option.excludes,
              };
            }
          });
        }
      });
      this.updateBoopsData(boops);
    },

    /**
     * Make API call to create box or option
     */
    async createBoop(payload) {
      const url = this.searchType === "option" ? "options" : "boxes";
      return await this.api.post(url, payload);
    },

    /**
     * Handle successful boop creation and update local state
     */
    handleBoopCreation(response) {
      const boops = this.localSelectedBoops ? _.cloneDeep(this.localSelectedBoops) : [];

      if (this.searchType === "box") {
        boops.push(response.data);
        this.boopsStore.getBoxes();
      } else {
        const i = this.selectedBox.index;

        if (boops[i].ops) {
          boops[i].ops.push(response.data);
        } else {
          Object.assign(boops[i], { ops: [response.data] });
        }
        this.boopsStore.getOptions();
      }

      // Update boops data
      this.updateBoopsData(boops);
      this.handleSuccess(response);
      this.searchBoop = false;
    },
    remove(item, type, box_index, option_index) {
      this.removeItem = item;
      this.removeType = type;
      this.removeBoxIndex = parseInt(box_index);
      this.removeOptionIndex = parseInt(option_index);
    },
    /**
     * Simple method to remove box or option
     */
    removeBoop(item, type, box_index, option_index) {
      if (type === "box") {
        this.localSelectedBoops.splice(box_index, 1);
      } else {
        this.localSelectedBoops[box_index].ops.splice(option_index, 1);
      }

      // Update boops data
      this.updateBoopsData(this.localSelectedBoops);

      // Reset modal state
      this.removeType = "";
      this.removeItem = false;
      this.removeBoxIndex = null;
      this.removeOptionIndex = null;
    },
    async removeMultipleBoops(box_index) {
      try {
        await this.confirm({
          title: this.$t("remove options"),
          message: this.$t("this will remove the selected options from the box!"),
          confirmOptions: {
            label: this.$t("remove"),
            variant: "danger",
          },
        });
        let i = 0;
        this.selectedOptionsList.forEach((option_id) => {
          i = this.localSelectedBoops[box_index].ops.findIndex((op) => op.id == option_id);
          this.localSelectedBoops[box_index].ops.splice(i, 1);
        });

        // Update boops data
        this.updateBoopsData(this.localSelectedBoops);

        this.addToast({
          type: "info",
          message: this.$t("options succesfully removed"),
        });
      } catch (error) {
        if (error.cancelled) return;
        this.handleError(error);
      } finally {
        this.selectedOptionsList = [];
      }
    },
    closeModal() {
      this.removeItem = false;
    },
    async editBox(e) {
      try {
        const response = await this.api.get(`/boxes/${e.slug}`);

        const freshBox = _.cloneDeep(response.data);

        this.menuItemClicked("editBox", freshBox);
      } catch (error) {
        this.handleError(error);
      }
    },
    async menuItemClicked(event, item) {
      switch (event) {
        case "editOption":
          await this.api
            .get(`categories/${this.selected_category.id}/options/${item.id}`)
            .then((response) => {
              this.set_item(response.data);
              this.set_runs(response.data.sheet_runs);
              this.set_runs(response.data.runs);
              this.set_flag("from_boops");
              this.component = "OptionsEditPanel";
            });
          break;

        case "editBox":
          await this.set_item(item);
          this.set_flag("from_boops");
          this.component = "BoxesEditPanel";
          break;

        default:
          break;
      }
    },
    calcRef(calc_ref) {
      switch (calc_ref) {
        case "format":
          return "ruler-combined";
        case "material":
          return "file";
        case "weight":
          return "weight-hanging";
        case "printing_colors":
          return "circles-overlap-3";
        default:
          return "check";
      }
    },
  },
};
</script>

<style scoped>
.list {
  position: relative; /* position of list container must be set to `relative` */
}
/* dragging item will be added with a `dragging` class */
/* so you can use this class to define the appearance of it */
.list > *.dragging {
  box-shadow: 0 2px 10px 0 rgba(0, 0, 0, 0.2);
}

.ghost {
  @apply border-2 border-dashed border-gray-400 bg-gray-200 opacity-50;
}

.sortable-chosen {
  @apply border-theme-500;
}
</style>
