<template>
  <div class="w-full">
    <p
      v-if="selected_option && Object.keys(selected_option).length === 0"
      class="mx-auto mt-2 flex w-full items-center px-4 text-sm font-bold uppercase tracking-wide"
    >
      <!-- <font-awesome-icon
        :icon="['fad', 'circle-info']"
        class="mr-2 text-sm rounded-full cursor-pointer text-theme-500 hover:bg-theme-100"
      /> -->

      {{ $t("select option") }}

      <VMenu theme="tooltip" class="ml-2">
        <!-- This will be the popover reference (for the events and position) -->
        <font-awesome-icon :icon="['fal', 'circle-info']" class="fa-fw text-theme-500" />

        <!-- This will be the content of the popover -->
        <template #popper>
          <div class="flex max-w-80 flex-col p-4">
            <p>
              {{
                capitalizeFirstLetter(
                  //prettier-ignore
                  $t( `select your excludes here to manage what options can't be selected, when another option is selected. `),
                )
              }}

              <br />
              <br />
              {{
                capitalizeFirstLetter(
                  $t(`
                    for example: a brochure with a glued cover and the option for spiral binding`),
                )
              }}
            </p>
          </div>
        </template>
      </VMenu>
    </p>

    <transition name="slide">
      <div
        v-if="Object.keys(selected_option).length > 0"
        class="mx-auto flex w-full items-center justify-center p-2"
      >
        <p class="flex items-center">
          <!-- <span class="text-sm font-bold uppercase tracking-wide">{{ $t("managing") }}:</span> -->
          <span
            class="mx-2 mr-2 flex items-center justify-between rounded border bg-gray-50 p-2 text-lg"
          >
            <span class="flex flex-col font-bold">
              <span
                v-if="selected_divider && manageExcludes !== 'multiple'"
                class="text-xs font-normal uppercase tracking-wide text-gray-500"
              >
                {{ selected_divider }}
              </span>
              {{ $display_name(selected_option.display_name) }}
            </span>
            <button
              class="ml-4 cursor-pointer rounded-full px-2 py-1 text-sm text-theme-500 hover:bg-theme-100"
              @click="(set_selected_option({}), (manageExcludes = false))"
            >
              <font-awesome-icon :icon="['fal', 'times']" class="fa-fw" />
              {{ $t("Cancel") }}
            </button>
          </span>
        </p>
        <font-awesome-icon :icon="['fas', 'arrow-right']" class="mx-4 text-2xl text-gray-400" />
        <div class="flex flex-col items-start">
          <span v-if="manageExcludes" class="w-full text-center text-theme-500">
            <font-awesome-icon
              :icon="['fad', 'circle-info']"
              class="mr-2 cursor-pointer rounded-full text-sm text-theme-500 hover:bg-theme-100"
            />
            {{ $t("select option to exclude") }}
          </span>
          <button
            v-if="!manageExcludes"
            class="rounded-full px-2 py-1 text-sm text-red-500 hover:bg-red-100"
            @click="manageExcludes = 'single'"
          >
            <font-awesome-icon :icon="['fal', 'clipboard-list-check']" />
            {{ $t("single excludes") }}
            <font-awesome-icon
              v-tooltip="
                $t('You are selecting options which will not be selectable with ') +
                $display_name(selected_option.display_name)
              "
              :icon="['fas', 'circle-info']"
            />
          </button>
          <button
            v-if="!manageExcludes"
            class="rounded-full px-2 py-1 text-sm text-orange-500 hover:bg-orange-100"
            @click="manageExcludes = 'multiple'"
          >
            <font-awesome-icon :icon="['fal', 'chart-network']" />
            {{ $t("excludes with another option") }}
            <font-awesome-icon
              v-tooltip="
                $t('You are selecting options which will not be selectable with ') +
                $display_name(selected_option.display_name) +
                $t(' in combination with other options')
              "
              :icon="['fas', 'circle-info']"
            />
          </button>
        </div>
      </div>
    </transition>

    <section class="w-full">
      <ExcludesOverview
        v-if="!manageExcludes"
        :key="excludesOverviewKey"
        :change-manage-excludes="changeManageExcludes"
        :manage-excludes="manageExcludes"
        :divided="divided"
        :selected-boops="selected_boops"
        :selected-box="selected_box"
        :selected-option="selected_option"
        :selected-divider="selected_divider"
      />
      <SingleExcludes
        v-if="manageExcludes === 'single'"
        :change-manage-excludes="changeManageExcludes"
        :manage-excludes="manageExcludes"
        :divided="divided"
        :selected-boops="selected_boops"
        :selected-box="selected_box"
        :selected-option="selected_option"
        :selected-divider="selected_divider"
      />
      <MultipleExcludes
        v-if="manageExcludes === 'multiple'"
        :change-manage-excludes="changeManageExcludes"
        :manage-excludes="manageExcludes"
        :divided="divided"
        :selected-boops="selected_boops"
        :selected-box="selected_box"
        :selected-option="selected_option"
        :selected-divider="selected_divider"
      />
    </section>
  </div>
</template>

<script>
/**
 * ManageExcludes Component - Exclude Management Orchestrator
 *
 * Central controller coordinating three exclude management modes:
 * ExcludesOverview (main view), SingleExcludes (1:1), MultipleExcludes (many:many)
 *
 * Mode control via `manageExcludes` state, context tracking for selected options,
 * dual-store pattern with props priority, reactive key for force re-renders
 *
 * @component ManageExcludes
 * @since 1.0.0
 */

import { mapState, mapMutations } from "vuex";
import ExcludesOverview from "./ExcludesOverview.vue";
import SingleExcludes from "./SingleExcludes.vue";
import MultipleExcludes from "./MultipleExcludes.vue";

export default {
  components: {
    ExcludesOverview,
    SingleExcludes,
    MultipleExcludes,
  },
  props: {
    divided: {
      type: Boolean,
      required: false,
      default: false,
    },
    selectedBoops: {
      type: Array,
      required: false,
      default: () => [],
    },
    selectedBox: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    selectedOption: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    selectedDivider: {
      type: String,
      required: false,
      default: "",
    },
  },
  emits: ["onManagingExcludes", "onDoneManagingExcludes"],
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    return { capitalizeFirstLetter };
  },
  data() {
    return {
      manageExcludes: false,
      loading: false,
      localDivided: false,
    };
  },
  computed: {
    // Legacy VueX mappings - now mainly used as fallback
    ...mapState({
      vuex_selected_category: (state) => state.product_wizard.selected_category,
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,
      vuex_selected_box: (state) => state.product_wizard.selected_box,
      vuex_selected_option: (state) => state.product_wizard.selected_option,
      vuex_selected_divider: (state) => state.product_wizard.selected_divider,
    }),

    // Get data from parent component props or VueX fallback
    selected_category() {
      // Parent component manages the data flow now
      return this.$parent.selected_category || this.vuex_selected_category || {};
    },

    selected_boops() {
      return this.selectedBoops?.length > 0
        ? this.selectedBoops
        : this.$parent?.selected_boops || this.vuex_selected_boops || [];
    },

    selected_box() {
      return Object.keys(this.selectedBox).length > 0
        ? this.selectedBox
        : this.$parent?.selected_box || this.vuex_selected_box || {};
    },

    selected_option() {
      return Object.keys(this.selectedOption).length > 0
        ? this.selectedOption
        : this.$parent?.selected_option || this.vuex_selected_option || {};
    },

    selected_divider() {
      return (
        this.selectedDivider || this.$parent?.selected_divider || this.vuex_selected_divider || ""
      );
    },

    // Force ExcludesOverview to re-render when exclude data changes
    excludesOverviewKey() {
      // Create a key based on all excludes in all options to force re-render
      let excludeHash = "";
      if (this.selected_boops && Array.isArray(this.selected_boops)) {
        this.selected_boops.forEach((box, boxIndex) => {
          if (box.ops && Array.isArray(box.ops)) {
            box.ops.forEach((option, optionIndex) => {
              if (option.excludes && Array.isArray(option.excludes)) {
                excludeHash += `${boxIndex}-${optionIndex}-${option.excludes.length}-`;
              }
            });
          }
        });
      }
      return `excludes-${excludeHash}`;
    },
  },
  watch: {
    selected_boops: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    selected_box: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    selected_option: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    manageExcludes(v) {
      if (v) {
        this.$emit("onManagingExcludes");
      } else {
        this.$emit("onDoneManagingExcludes");
      }
    },
  },
  mounted() {
    // No longer setting localDivided - using divided prop directly
    // this.localDivided = this.divided || this.selected_category.divided;
  },
  unmounted() {
    // this.set_selected_boop_excludes([]);
    // this.set_selected_boops([]);
    this.set_selected_box({});
    this.set_selected_option({});
    this.set_selected_divider("");
  },
  methods: {
    // Legacy VueX mappings (kept for compatibility)
    ...mapMutations({
      vuex_set_selected_boops: "product_wizard/set_selected_boops",
      vuex_set_selected_box: "product_wizard/set_selected_box",
      vuex_set_selected_option: "product_wizard/set_selected_option",
      vuex_set_selected_divider: "product_wizard/set_selected_divider",
      vuex_set_generated_manifest: "product_wizard/set_generated_manifest",
      vuex_set_component: "product_wizard/set_wizard_component",
    }),

    // Delegate to parent component methods for data updates
    set_selected_boops(boops) {
      if (this.$parent.set_selected_boops) {
        this.$parent.set_selected_boops(boops);
      } else {
        this.vuex_set_selected_boops(boops);
      }
    },

    set_selected_box(box) {
      if (this.$parent.set_selected_box) {
        this.$parent.set_selected_box(box);
      } else {
        this.vuex_set_selected_box(box);
      }
    },

    set_selected_option(option) {
      if (this.$parent.set_selected_option) {
        this.$parent.set_selected_option(option);
      } else {
        this.vuex_set_selected_option(option);
      }
    },

    set_selected_divider(divider) {
      if (this.$parent.set_selected_divider) {
        this.$parent.set_selected_divider(divider);
      } else {
        this.vuex_set_selected_divider(divider);
      }
    },

    changeManageExcludes(value) {
      this.manageExcludes = value;
    },
  },
};
</script>
