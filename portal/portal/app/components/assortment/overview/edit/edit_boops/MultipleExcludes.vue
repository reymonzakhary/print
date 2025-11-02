<template>
  <section class="relative flex flex-col">
    <nav
      class="sticky left-0 my-8 flex flex-wrap items-center rounded bg-white p-2 dark:bg-gray-700 lg:w-2/3"
    >
      <div v-for="(id, i) in excludeWith" :key="id" class="flex items-center pr-2">
        <span v-if="idInfo(id)" class="flex flex-col font-bold">
          <span
            v-if="selected_divider"
            class="text-xs font-normal uppercase tracking-wide text-gray-500"
          >
            {{ selected_divider }}
          </span>
          {{ $display_name(idInfo(id).display_name) }}
        </span>
        <span v-if="i + 1 !== excludeWith.length" class="ml-2 text-gray-500 dark:text-gray-400">
          &amp;
        </span>
      </div>

      <button
        v-if="step === 2"
        class="mx-auto rounded-full border border-theme-500 px-2 text-sm text-theme-500 hover:bg-theme-100 dark:border-theme-400 dark:text-theme-400"
        @click="step = 1"
      >
        {{ $t("edit") }}
      </button>

      <div v-if="step === 1" class="px-2 italic text-gray-500 dark:text-gray-400">
        {{ $t("select more below") }}
      </div>

      <div v-if="excludeWith.length > 1 && step === 1" class="px-2 text-gray-500">
        -- {{ $t("or") }} --
      </div>

      <div v-if="excludeWith.length > 1 && step === 2" class="mx-20 flex items-center px-2">
        <font-awesome-icon
          :icon="['fal', 'link-slash']"
          class="mx-2 text-red-500 dark:text-red-400"
        />

        <span v-for="(ex, index) in exclude" :key="index" class="ml-20 flex flex-col font-bold">
          <span
            v-if="selected_divider"
            class="text-xs font-normal uppercase tracking-wide text-gray-500"
          >
            {{ selected_divider }}
          </span>
          {{ $display_name(idInfo(ex).display_name) }}
          <span v-if="exclude[index + 1]"> & </span>
        </span>
      </div>

      <button
        v-if="excludeWith.length > 1 && step === 1"
        class="mx-auto rounded-full border border-theme-500 px-2 text-sm text-theme-500 hover:bg-theme-100"
        @click="step = 2"
      >
        {{ $t("select exclude") }}
      </button>
      <button
        v-if="excludeWith.length > 1 && exclude"
        class="ml-auto px-2 text-sm font-bold uppercase text-green-500 hover:text-green-600"
        @click="excludesToBoops"
      >
        {{ $t("done") }}
      </button>
    </nav>

    <div class="flex flex-wrap">
      <template v-for="(box, index) in selected_boops" :key="index">
        <div
          v-if="selected_divider === box.divider || !divided"
          class="relative flex"
          :class="{
            'mx-1 my-4 flex rounded border border-gray-200 px-2 py-4 dark:border-gray-500': divided,
            'ml-0 rounded-l-none !border-l-0 !pl-0':
              selected_boops[index - 1]?.divider === box.divider,
            'mr-0 rounded-r-none !border-r-0 !pr-0':
              selected_boops[index + 1]?.divider === box.divider,
          }"
        >
          <div
            v-if="
              divided &&
              (selected_boops[index - 1]?.divider !== box.divider || (index === 0 && divided))
            "
            class="absolute mx-auto -mt-7 flex items-center bg-white px-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-800"
          >
            {{ box.divider }}

            <font-awesome-icon
              :icon="['fal', 'calculator']"
              class="fa-lg ml-4 mr-2 text-gray-300 dark:text-gray-500"
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
              v-if="step === 2 && !box.ops.find((op) => excludeWith.includes(op.id))"
              class="my-2 h-8 w-full border-b px-2 text-theme-500 hover:cursor-pointer hover:text-theme-700"
              @click="Manage_multiple_entire_excludes(box.id, divided)"
            >
              <template v-if="!box.ops.find((op) => op.id === selected_option.id)">
                <font-awesome-icon
                  v-if="checkIfEntireBox(box.divider, box.id)"
                  :icon="['fas', 'times-square']"
                  class="mr-2 text-red-500"
                />
                <font-awesome-icon v-else :icon="['fal', 'square']" class="mr-2 text-theme-500" />
                {{ $t("exclude entire box") }}
              </template>
            </div>
            <ul>
              <li
                v-for="(item, idx) in box.ops"
                :key="idx"
                class="my-2 flex w-52 items-center rounded px-2 hover:bg-gray-200 dark:hover:bg-gray-800"
                :class="{
                  'border border-theme-500 text-theme-500 hover:bg-white dark:border-theme-400 dark:text-theme-400':
                    (selected_option.id === item.id || excludeWith.includes(item.id)) &&
                    (!divided || selected_divider === box.divider),
                  'border border-red-500 text-red-500 hover:bg-white dark:border-red-400 dark:text-red-400':
                    exclude.includes(item.id),
                  'text-gray-500':
                    boxExcludeSelected.includes(box.id) &&
                    (!divided || selected_divider === box.divider),
                }"
                @click.stop="
                  step === 1 &&
                  (!boxExcludeSelected.includes(box.id) || excludeWith.includes(item.id))
                    ? toggleExcludeWith(item.id, box.id)
                    : exclude.includes(item.id) || boxExcludeSelected.includes(box.id)
                      ? exclude.slice(
                          exclude.findIndex((x) => x === item.id),
                          1,
                        )
                      : exclude.push(item.id)
                "
              >
                <div class="flex w-full cursor-pointer items-center">
                  <template v-if="selected_option.id !== item.id && step === 1">
                    <font-awesome-icon
                      v-if="excludeWith.includes(item.id)"
                      :icon="['fas', 'square-check']"
                      class="mr-2 text-theme-500 dark:text-theme-400"
                    />
                    <font-awesome-icon
                      v-else
                      :icon="['fal', 'square']"
                      class="mr-2 text-theme-500 dark:text-theme-400"
                    />
                  </template>

                  <template
                    v-if="
                      selected_option.id !== item.id &&
                      !excludeWith.includes(item.id) &&
                      step === 2 &&
                      !boxExcludeSelected.includes(box.id) &&
                      (!exclude || exclude.includes(item.id))
                    "
                  >
                    <font-awesome-icon
                      v-if="exclude.includes(item.id)"
                      :icon="['fas', 'times-square']"
                      class="mr-2 text-red-500 dark:text-red-400"
                    />
                    <font-awesome-icon
                      v-else
                      :icon="['fal', 'square']"
                      class="mr-2 text-theme-500 dark:text-theme-400"
                    />
                  </template>

                  <font-awesome-icon
                    v-if="exclude.includes(item.id)"
                    :icon="['fal', 'link-slash']"
                    class="mr-2 text-red-500 dark:text-red-400"
                  />
                  <font-awesome-icon
                    v-if="
                      excludeWith[0] === item.id && (!divided || selected_divider === box.divider)
                    "
                    :icon="['fal', 'crown']"
                    class="mr-2 text-theme-500 dark:text-theme-400"
                  />
                  <font-awesome-icon
                    v-if="excludeWith[0] !== item.id && excludeWith.includes(item.id)"
                    :icon="['fal', 'link']"
                    class="mr-2 text-theme-500 dark:text-theme-400"
                  />
                  {{ $display_name(item.display_name) }}
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
  </section>
</template>

<script>
/**
 * MultipleExcludes Component - Complex Many-to-Many Exclude Management
 *
 * Handles creation of grouped exclude relationships where combinations of options
 * exclude other options (e.g., A4 + Biotop Natural ↔ 135gsm paper weight).
 *
 * Two-step workflow: Select option group → Select exclude targets
 * Storage format: `option.excludes = [[optionId1, optionId2]]`
 * Uses VueX mutation `set_selected_boop_multiple_excludes` for batch processing
 *
 * @component MultipleExcludes
 * @since 1.0.0
 */

import { mapState, mapMutations } from "vuex";

export default {
  name: "MultipleExcludes",
  props: {
    manageExcludes: {
      type: [String, Boolean],
      required: true,
    },
    changeManageExcludes: {
      type: Function,
      required: true,
    },
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
  data() {
    return {
      boxExcludeSelected: [],
      excludeWith: [],
      exclude: [],
      step: 1,
      flatOptions: [],
      boops: [], // Internal working copy of selected_boops
    };
  },
  computed: {
    // Legacy VueX mappings (kept for backward compatibility)
    ...mapState({
      vuex_selected_boops: (state) => state.product_wizard.selected_boops,
      vuex_selected_box: (state) => state.product_wizard.selected_box,
      vuex_selected_option: (state) => state.product_wizard.selected_option,
      vuex_selected_divider: (state) => state.product_wizard.selected_divider,
    }),

    /**
     * DUAL-STORE DATA ACCESS PATTERN
     *
     * Priority order:
     * 1. Props from parent (selectedBoops, selectedBox, etc.)
     * 2. VueX store fallback for backward compatibility
     * 3. Sensible defaults
     */
    selected_category() {
      return this.$parent.selected_category || {};
    },
    selected_boops() {
      return this.selectedBoops && this.selectedBoops.length > 0
        ? this.selectedBoops
        : this.vuex_selected_boops || [];
    },
    selected_box() {
      return Object.keys(this.selectedBox || {}).length > 0
        ? this.selectedBox
        : this.vuex_selected_box || {};
    },
    selected_option() {
      return Object.keys(this.selectedOption || {}).length > 0
        ? this.selectedOption
        : this.vuex_selected_option || {};
    },
    selected_divider() {
      return this.selectedDivider || this.vuex_selected_divider || "";
    },
  },
  watch: {
    selected_boops: {
      deep: true,
      immediate: true,
      handler(newVal) {
        if (newVal && Array.isArray(newVal)) {
          // Clone the data to avoid direct mutation of props
          this.boops = JSON.parse(JSON.stringify(newVal));
        }
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
    selected_divider: {
      handler(newVal) {
        return newVal;
      },
    },
  },
  mounted() {
    setTimeout(() => {
      this.excludeWith.push(this.selected_option.id);
    }, 100);

    this.flattenOptions();

    this.boxExcludeSelected.push(this.selected_box.id);
  },

  methods: {
    ...mapMutations({
      set_selected_boops: "product_wizard/set_selected_boops",
      set_selected_boop_excludes: "product_wizard/set_selected_boop_multiple_excludes",
    }),

    excludesToBoops() {
      const mutations = [];

      this.exclude.forEach((excl) => {
        const excludes = [];

        this.excludeWith.forEach((exclWith) => {
          excludes.push(exclWith);
        });
        excludes.push(excl);
        // console.log(newEx,excludes);
        // Create excludes directly in the data structure (like SingleExcludes does)
        // Collect all mutation data first (before any mutations change the store)

        excludes.forEach((optionId) => {
          // Find the option in the STORE's selected_boops (before any mutations)
          let foundBox = null;
          let foundOption = null;

          for (let i = 0; i < this.selected_boops.length; i++) {
            const boop = this.selected_boops[i];
            const option = boop.ops?.find((op) => op.id === optionId);

            if (option) {
              foundBox = boop;
              foundOption = option;
              break;
            }
          }

          if (foundBox && foundOption) {
            // Get all other options in the exclude group (not including this option)
            const otherOptionsInGroup = excludes.filter((id) => id !== optionId);

            const mutationData = {
              box_id: foundBox.id,
              option_id: optionId,
              excludes: otherOptionsInGroup,
              use_dividers: this.divided, // Pass whether to consider dividers
            };

            mutations.push({
              mutationData,
              optionName: foundOption.name,
              boxName: foundBox.name,
            });
          } else {
            console.error("❌ Could not find option in store:", { optionId, excludes });
          }
        });

        console.log("� Collected mutations for all options:", mutations);
      });

      // Now execute all mutations
      mutations.forEach(({ mutationData, optionName, boxName }) => {
        // Use the specific multiple excludes mutation
        this.set_selected_boop_excludes(mutationData);
      });

      // Get updated boops from store (mutations updated the store directly)
      this.boops = this.selected_boops;

      this.excludeWith = [];
      this.exclude = [];

      this.changeManageExcludes(false);
    },

    toggleExcludeWith(id, box_id) {
      this.toggleBoxSelectedExclude(box_id);
      if (this.excludeWith.length > 0) {
        if (this.excludeWith.findIndex((x) => x === id) >= 0) {
          const i = this.excludeWith.findIndex((x) => x === id);
          this.excludeWith.splice(i, 1);
        } else {
          this.excludeWith.push(id);
        }
      } else {
        this.excludeWith.push(id);
      }
    },
    toggleBoxSelectedExclude(id) {
      if (this.boxExcludeSelected.length > 0) {
        if (this.boxExcludeSelected.findIndex((x) => x === id) >= 0) {
          const i = this.boxExcludeSelected.findIndex((x) => x === id);
          this.boxExcludeSelected.splice(i, 1);
        } else {
          this.boxExcludeSelected.push(id);
        }
      } else {
        this.boxExcludeSelected.push(id);
      }
    },

    Manage_multiple_entire_excludes(boxId, divider = null) {
      const box = this.boops.find((box) => {
        if (divider) {
          return box.id === boxId && box.divider === divider;
        } else {
          return box.id === boxId;
        }
      });
      const allOptions = box.ops.map((option) => option.id);
      if (this.exclude > 0 || !allOptions.every((ex) => this.exclude.includes(ex))) {
        this.exclude = allOptions;
      } else {
        this.exclude = [];
      }
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
        return this.exclude.includes(option.id);
      });
    },

    idInfo(id) {
      for (let i = 0; i < this.flatOptions.length; i++) {
        const option = this.flatOptions[i];
        if (option.id === id) {
          return option;
        }
      }
    },

    flattenOptions() {
      const arr = [];
      for (let i = 0; i < this.boops.length; i++) {
        const box = this.boops[i];
        arr.push(box.ops);
      }
      this.flatOptions = [].concat(...arr);
    },

    checkState(divider, option_id) {
      if (
        this.excludeWith.findIndex((x) => x.length > 0 && x.includes(option_id)) >= 0 &&
        (!divider || this.selected_divider === divider)
      ) {
        return true;
      } else {
        return false;
      }
    },
  },
};
</script>

<style></style>
