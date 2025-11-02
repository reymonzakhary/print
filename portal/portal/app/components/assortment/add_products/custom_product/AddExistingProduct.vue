<template>
  <!-- general item overview component -->
  <div class="relative h-full w-full rounded">
    <section class="flex flex-wrap justify-center">
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

      <div
        class="top-4 z-20 flex w-full justify-center border-b p-6 pb-0 dark:border-gray-800"
        :class="{ sticky: stickySearch, 'bg-white dark:bg-gray-700': wizardMode }"
      >
        <form v-if="!selected" class="flex w-full pb-4" @submit.prevent>
          <input
            ref="searchBar"
            v-model="searchQuery"
            type="text"
            :placeholder="`Search ${type}`"
            class="input mx-auto border-2 border-theme-400 shadow-lg dark:border-theme-400 dark:shadow-gray-800 md:w-1/2"
            @input="searchItems"
          />
        </form>
      </div>

      <section
        v-if="items && items.length > 0 && !selected"
        class="z-10 mt-6 flex w-full flex-wrap"
      >
        <div v-for="(item, i) in items" :key="item.name + i" class="flex w-full items-center">
          <div
            class="group mx-auto my-3 flex w-full cursor-pointer items-stretch justify-between rounded border-2 bg-white p-2 px-4 transition hover:bg-theme-50 hover:text-theme-500 hover:shadow-xl focus:shadow-none dark:bg-gray-700 dark:hover:border-theme-400 dark:hover:bg-theme-900"
            @click="(set_selected_search_item(item), set_name(item.name), (selected = true))"
          >
            <div class="flex w-1/2 items-center p-2">
              <figure class="mr-2 flex items-center">
                <UITenantLogo :logo-url="tenantLogo" :skip-fetch="true" class="max-h-8 max-w-8" />

                <img
                  v-if="getImgUrl(item.name?.toLowerCase())"
                  :src="getImgUrl(item.name?.toLowerCase())"
                  :alt="item.name"
                  class="size-10"
                />

                <div v-else class="ml-2 size-8 rounded-full bg-gray-200 dark:bg-gray-700">
                  <font-awesome-icon
                    :icon="['fal', 'image']"
                    class="m-2 text-gray-400 dark:text-gray-500"
                  />
                </div>
              </figure>

              <div class="">
                <span class="w-full text-sm text-gray-500">{{ $t("Display name") }}</span>
                <p
                  class="shrink-0 whitespace-nowrap text-xl"
                  @click="
                    (set_selected_search_item(item),
                    set_display_name($display_name(item.display_name)),
                    (selected = true))
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

                  <template v-else>
                    <span
                      v-for="(part, index) in highlightName(item.name)"
                      :key="index"
                      class="truncate"
                      :class="{ 'font-semibold text-theme-500': part.highlight }"
                    >
                      {{ part.text }}
                    </span>
                  </template>
                </p>
              </div>
            </div>

            <div class="flex w-1/2 items-center justify-end">
              <font-awesome-icon
                v-if="item?.calc_ref || item.additional?.calc_ref"
                class="ml-1 mr-6 text-sm text-theme-400"
                fixed-width
                :icon="[
                  'fal',
                  calcRef(type === 'box' ? item?.calc_ref : item.additional?.calc_ref),
                ]"
              />
              <div class="flex flex-col justify-center text-right">
                <span class="w-full text-xs text-gray-500">{{ $t("Original name") }}</span>
                <small
                  v-tooltip="$t('original name') + ': ' + $display_name(item.name)"
                  class="ml-2 truncate text-right text-gray-500"
                >
                  {{ item.name }}
                </small>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ITEM NOT FOUND -->
      <section v-else-if="items && items.length === 0 && !selected" class="w-full lg:w-1/2">
        <div class="my-10 w-full rounded bg-white p-4 text-center shadow-md dark:bg-gray-700">
          <div v-if="searchQuery" class="w-full text-center">
            <b>{{ searchQuery }}</b> {{ $t("not found") }}...
          </div>

          <div v-else class="w-full text-center">
            {{ type === "box" ? $t(`There is not boxes`) : $t(`There is not options`) }}
          </div>

          <button
            class="my-4 rounded bg-theme-400 px-2 py-1 text-themecontrast-400"
            @click="$emit('toggleToCreate')"
          >
            {{ type === "box" ? $t("Create new box") : $t("Create new option") }}
          </button>
        </div>
      </section>

      <!-- Add selected item as -->
      <section v-else-if="selected && selected_search_item" class="md:w-2/3">
        <div class="relative mt-10 items-center rounded border-2 bg-white dark:bg-gray-700">
          <div class="flex items-center justify-between bg-gray-100 px-4">
            <p class="flex-1">{{ $t("adding item") }}</p>
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

          <div class="mt-10 flex w-full">
            <button
              class="ml-auto mr-4 rounded-full border-2 border-theme-500 px-4 py-2 text-theme-500 transition-all hover:bg-theme-400 hover:text-themecontrast-400"
              @click="$emit('addNew')"
            >
              <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
              {{ type === "box" ? $t("Create Box") : $t("Create Option") }}
              <font-awesome-icon :icon="['fas', 'angle-right']" class="ml-2" />
            </button>
          </div>
        </div>
      </section>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import mixin from "~/components/assortment/add_products/customProductMixin.js";
import _ from "lodash";

export default {
  mixins: [mixin],
  props: {
    nextComponent: {
      type: String,
      default: "PrintProductEditBoops",
    },
    type: {
      required: false,
      type: String,
      default: "item",
    },
    stickySearch: {
      type: Boolean,
      default: true,
    },

    wizardType: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["addNew", "toggleToCreate"],
  setup() {
    const boopsStore = useBoxesAndOptions();
    const api = useAPI();
    const items = ref([]);
    const searchQuery = ref("");
    const tenantLogo = ref(null);

    // Fetch tenant logo once for all components
    const fetchTenantLogo = async () => {
      try {
        const info = await api.get("/info");
        tenantLogo.value = info.logo;
      } catch (error) {
        console.error("Error fetching info:", error);
      }
    };

    onMounted(() => {
      fetchTenantLogo();
    });

    return { api, boopsStore, items, searchQuery, tenantLogo };
  },
  data() {
    return {
      selected: false,
    };
  },
  computed: {
    ...mapState({
      selected_search_item: (state) => state.product_wizard.selected_search_item,
      selected_producer: (state) => state.product_wizard.selected_producer,
      name: (state) => state.product_wizard.name,
      display_name: (state) => state.product_wizard.display_name,
      search: (state) => state.product_wizard.search,
      system_key: (state) => state.product_wizard.system_key,
    }),
  },
  watch: {
    search: _.debounce(function (v) {
      this.api.get(`${this.searchUrl}${v}&iso=${this.$i18n.locale}`).then((response) => {
        this.items = response.data.suggestions ? response.data.suggestions : response.data;
        this.set_name(v);
      });
    }, 300),
    selected_search_item(v) {
      this.set_display_name(v.display_name);
      this.set_name(v.name);
      return v;
    },
    word(v) {
      const check = new RegExp(query, "ig");
      return v.toString().replace(check, function (matchedText) {
        return `<strong class='${classList}'>` + matchedText + "</strong>";
      });
    },
  },
  beforeUnmount() {
    this.set_search("");
  },
  async created() {
    if (this.type === "box") {
      if (this.boopsStore.boxes.length === 0) {
        this.items = await this.boopsStore.getBoxes();
      }
      this.items = this.boopsStore.boxes;
    }
    if (this.type === "option") {
      if (this.boopsStore.options.length === 0) {
        this.items = await this.boopsStore.getOptions();
      }
      this.items = this.boopsStore.options;
    }
  },
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
    searchItems() {
      this.selected = false;
      if (this.type === "box") {
        this.items = this.boopsStore.boxes.filter((item) => {
          return (
            item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            item.display_name.find((dName) =>
              dName.display_name.toLowerCase().includes(this.searchQuery.toLowerCase()),
            )
          );
        });
      } else if (this.type === "option") {
        this.items = this.boopsStore.options.filter((item) => {
          return (
            item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            item.display_name.find((dName) =>
              dName.display_name.toLowerCase().includes(this.searchQuery.toLowerCase()),
            )
          );
        });
      }
    },
    selectLinked(item, linked) {
      this.set_selected_search_item(item);
      setTimeout(() => {
        this.set_display_name(this.$display_name(linked.display_name));
        this.selected = true;
      }, 200);
    },

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
     * HIGHLIGHT SEARCH TERMS IN RESULTS
     *
     * Creates visual highlighting for search term matches in result names.
     * Returns array of text segments with highlight flags for UI rendering.
     *
     * REGEX ESCAPING: Safely handles special regex characters in search terms
     * CASE INSENSITIVE: Uses 'ig' flags for inclusive matching
     */
    highlightName(name) {
      if (!this.searchQuery || this.searchQuery.length === 0) {
        return [{ text: name, highlight: false }];
      }

      // Escape special regex characters to prevent regex injection
      const escapedSearch = this.searchQuery.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
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
