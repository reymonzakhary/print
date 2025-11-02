<template>
  <div class="mx-auto w-full p-4">
    <div v-if="selected_boops" class="flex flex-wrap">
      <template v-for="(box, index) in selected_boops" :key="index">
        <div
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
            class="absolute mx-auto -mt-7 flex items-center bg-white px-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-700"
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

          <nav
            v-tooltip="getExcludeTooltip(box)"
            class="m-2 w-full rounded border-2 p-2 dark:bg-gray-700"
            :class="hasCommonExclude(box.id, box.divider) ? 'bg-gray-200' : 'bg-white'"
          >
            <h2 class="pl-2 text-sm font-bold uppercase tracking-wide">
              {{ $display_name(box.display_name) }}
            </h2>
            <ul>
              <li
                v-for="(item, idx) in box.ops"
                :key="idx"
                class="my-2 flex w-52 items-center rounded px-2 hover:bg-gray-200 dark:hover:bg-gray-900"
                :class="{
                  'bg-theme-100 text-theme-500':
                    selected_option &&
                    selected_option.id === item.id &&
                    selected_divider === box.divider,
                }"
                @click="setSelected(box, item, box.divider ? box.divider : null)"
              >
                <div class="flex items-center">
                  <VDropdown @click="setSelected(box, item, divided ? box.divider : null)">
                    <font-awesome-icon
                      v-tooltip="
                        item.excludes &&
                        item.excludes.filter((a) => a.length > 0 && a.length < 2).length > 0
                          ? `Has ${
                              item.excludes.filter((a) => a.length > 0 && a.length < 2).length
                            } excludes`
                          : 'has no excludes'
                      "
                      fixed-with
                      :icon="[
                        item.excludes &&
                        item.excludes.filter((a) => a.length > 0 && a.length < 2).length > 0
                          ? 'fad'
                          : 'fal',
                        'clipboard-list-check',
                      ]"
                      :class="[
                        item.excludes &&
                        item.excludes.filter((a) => a.length > 0 && a.length < 2).length > 0
                          ? 'cursor-pointer text-red-500 dark:text-red-400'
                          : 'text-gray-300',
                      ]"
                    />
                    <template
                      v-if="
                        item.excludes &&
                        item.excludes.filter((a) => a.length > 0 && a.length < 2).length > 0
                      "
                      #popper
                    >
                      <section
                        class="divide-y divide-gray-800 rounded border border-red-500 bg-gray-900 p-4 text-red-500 shadow-md dark:border-red-400 dark:bg-gray-800 dark:text-red-400"
                      >
                        <div
                          v-for="(exclude, i) in item.excludes"
                          :key="`exclude_${i}`"
                          class="flex justify-between py-1"
                        >
                          <template v-if="exclude.length === 1">
                            <span v-if="idInfo(exclude[0])">
                              {{ $display_name(idInfo(exclude[0]).display_name) }}
                            </span>
                            <button
                              class="mx-2 rounded-full border border-red-500 px-2 text-red-500 hover:bg-red-100 dark:border-red-400 dark:text-red-400"
                              @click="removeExclude(box.divider, item.excludes, item.id, i)"
                            >
                              <font-awesome-icon :icon="['fal', 'trash-can']" class="mr-2" />
                              {{ $t("remove") }}
                            </button>
                          </template>
                        </div>
                      </section>
                    </template>
                  </VDropdown>

                  <!-- display combined excludes -->
                  <VDropdown @click="setSelected(box, item, divided ? box.divider : null)">
                    <font-awesome-icon
                      v-tooltip="
                        item.excludes && item.excludes.filter((a) => a.length > 1).length > 0
                          ? `Has ${
                              item.excludes && item.excludes.filter((a) => a.length > 1).length
                            } excludes with other option(s)`
                          : 'has no excludes with other option(s)'
                      "
                      fixed-with
                      :icon="[
                        item.excludes && item.excludes.filter((a) => a.length > 1).length > 0
                          ? 'fad'
                          : 'fal',
                        'chart-network',
                      ]"
                      :class="[
                        item.excludes && item.excludes.filter((a) => a.length > 1).length > 0
                          ? 'cursor-pointer text-orange-500'
                          : 'text-gray-300',
                      ]"
                      class="ml-1"
                    />
                    <template
                      v-if="item.excludes && item.excludes.filter((a) => a.length > 1).length > 0"
                      #popper
                    >
                      <section
                        class="divide-y divide-gray-800 rounded border border-orange-500 bg-gray-900 p-4 text-orange-500 shadow-md"
                      >
                        <div
                          v-for="(exclude, index) in item.excludes"
                          :key="`exclude_${index}`"
                          class="flex items-center justify-between"
                        >
                          <template v-if="exclude.length > 1">
                            <div class="flex">
                              <template v-if="exclude.length > 1">
                                <span class="font-bold">
                                  {{ $display_name(item.display_name) }}
                                  <span class="mx-2 font-normal"> &amp; </span>
                                </span>
                                <template v-for="(excl, i) in exclude">
                                  <span
                                    v-if="idInfo(excl) && i + 1 < exclude.length"
                                    :key="`excl_${i}`"
                                  >
                                    <span class="font-bold">
                                      {{ $display_name(idInfo(excl).display_name) }}
                                    </span>
                                    <span v-if="i + 2 < exclude.length" class="mx-2"> &amp; </span>
                                  </span>
                                </template>
                              </template>
                            </div>
                            <font-awesome-icon
                              :icon="['fal', 'link-slash']"
                              class="mx-5 text-red-500"
                            />
                            <div class="flex">
                              <template v-if="exclude.length > 1">
                                <template v-for="(excl, i) in exclude">
                                  <span
                                    v-if="idInfo(excl) && i + 1 === exclude.length"
                                    :key="'excl' + excl"
                                  >
                                    {{ $display_name(idInfo(excl).display_name) }}
                                  </span>
                                </template>
                              </template>
                            </div>
                            <button
                              class="mx-2 rounded-full border border-red-500 px-2 text-red-500 hover:bg-red-100"
                              @click="removeExclude(box.divider, item.excludes, item.id, index)"
                            >
                              <font-awesome-icon :icon="['fal', 'trash-can']" class="mr-2" />
                              {{ $t("remove") }}
                            </button>
                          </template>
                        </div>
                      </section>
                    </template>
                  </VDropdown>
                </div>
                <VDropdown>
                  <div
                    class="ml-2 block w-full cursor-pointer"
                    :class="{
                      'text-red-500': checkState(item.id),
                    }"
                  >
                    <font-awesome-icon
                      v-if="checkState(item.id)"
                      :icon="['fal', 'link-slash']"
                      class="text-red-500"
                    />
                    {{ $display_name(item.display_name) }}
                  </div>
                  <template #popper>
                    <div class="flex rounded border-black bg-gray-900 p-2 shadow-md">
                      <button
                        class="rounded-full px-2 py-1 text-sm text-red-500 hover:bg-red-100"
                        @click="changeManageExcludes('single')"
                      >
                        <font-awesome-icon :icon="['fal', 'clipboard-list-check']" />
                        {{ $t("single excludes") }}
                        <font-awesome-icon
                          v-tooltip="
                            'You are selecting options which will not be selectable with ' +
                            $display_name(selected_option.display_name)
                          "
                          :icon="['fas', 'circle-info']"
                        />
                      </button>
                      <button
                        class="rounded-full px-2 py-1 text-sm text-orange-500 hover:bg-orange-100"
                        @click="changeManageExcludes('multiple')"
                      >
                        <font-awesome-icon :icon="['fal', 'chart-network']" />
                        {{ $t("excludes with another option") }}
                        <font-awesome-icon
                          v-tooltip="
                            'You are selecting options which will not be selectable with ' +
                            $display_name(selected_option.display_name) +
                            ' in combination with other options'
                          "
                          :icon="['fas', 'circle-info']"
                        />
                      </button>
                    </div>
                  </template>
                </VDropdown>
              </li>
            </ul>
          </nav>
        </div>
      </template>
    </div>

    <div v-else>no boxes and options</div>
  </div>
</template>

<script>
/**
 * ExcludesOverview Component - Visual Exclude Relationship Display
 *
 * Provides comprehensive visual overview of all option exclusion relationships.
 * Serves as both display interface and entry point for creating new excludes.
 *
 * Features: Visual indicators, relationship popovers, divided mode support,
 * props-first architecture with VueX fallback, option selection and navigation
 *
 * @component ExcludesOverview
 * @since 1.0.0
 */

import { mapState, mapMutations } from "vuex";

export default {
  props: {
    changeManageExcludes: {
      type: Function,
      required: true,
    },
    manageExcludes: {
      type: [String, Boolean],
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
  setup() {
    const api = useAPI();
    const instance = getCurrentInstance();
    const parent = instance.parent;
    return { api, parent };
  },
  data() {
    return {
      flatOptions: [],
      isOpen: false,
      localDivided: false,
    };
  },
  computed: {
    // Use props and parent data instead of VueX state
    selected_category() {
      return this.$parent.selected_category || {};
    },
    selected_boops() {
      return this.selectedBoops;
    },
    selected_box() {
      return this.selectedBox;
    },
    selected_divider() {
      return this.selectedDivider;
    },
    selected_option() {
      return this.selectedOption;
    },
    generated_manifest() {
      return this.$parent.generated_manifest || {};
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
    selected_divider: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
  },
  mounted() {
    if (this.selected_boops) {
      this.flattenOptions();
    }

    // No longer computing localDivided - using divided prop directly
    // this.localDivided = this.divided || this.selected_category.divided;
  },
  methods: {
    ...mapMutations({
      set_selected_boops: "product_wizard/set_selected_boops",
      set_selected_box: "product_wizard/set_selected_box",
      set_selected_option: "product_wizard/set_selected_option",
      set_selected_divider: "product_wizard/set_selected_divider",
      // set_selected_boop_excludes: "product_wizard/set_selected_boop_excludes",
    }),

    setSelected(box, item, divider) {
      // Call parent (ManageExcludes) methods which will delegate up the chain
      if (this.$parent?.set_selected_box) {
        this.$parent.set_selected_box(box);
      } else {
        this.set_selected_box(box);
      }

      if (this.$parent?.set_selected_option) {
        this.$parent.set_selected_option(item);
      } else {
        this.set_selected_option(item);
      }

      if (divider) {
        if (this.$parent?.set_selected_divider) {
          this.$parent.set_selected_divider(divider);
        } else {
          this.set_selected_divider(divider);
        }
      }
    },
    hasCommonExclude(boxId, divider, needID = false) {
      const box = this.selected_boops.find((b) => {
        if (divider) {
          return b.divider === divider && b.id === boxId;
        } else {
          return b.id === boxId;
        }
      });

      if (!box) return needID ? [] : false;

      const options = box.ops;

      // Filter items that have excludes
      const itemsWithExcludes = options.filter(
        (item) => item.excludes && Array.isArray(item.excludes) && item.excludes.length > 0,
      );

      // Need all items to have excludes to find common ones
      if (itemsWithExcludes.length < options.length) {
        return needID ? [] : false;
      }

      // Get all unique IDs from the first item's excludes (flattened)
      const firstItemExcludeIds = [...new Set(itemsWithExcludes[0].excludes.flat())];

      // Find ALL common exclude IDs
      const commonExcludeIds = firstItemExcludeIds.filter((excludeId) => {
        return itemsWithExcludes.slice(1).every((item) => {
          const flattenedExcludes = item.excludes.flat();
          return flattenedExcludes.includes(excludeId);
        });
      });

      // If no common exclude found
      if (commonExcludeIds.length === 0) {
        return needID ? [] : false;
      }

      // If needID is true, return array of IDs
      if (needID) {
        return commonExcludeIds;
      }

      // Return true if common exclude exists
      return true;
    },

    getExcludeTooltip(box) {
      if (!this.hasCommonExclude(box.id, box.divider)) {
        return "";
      }

      const excludeIds = this.hasCommonExclude(box.id, box.divider, true);

      if (!excludeIds || excludeIds.length === 0) {
        return "";
      }

      const displayNames = excludeIds
        .map((id) => {
          const info = this.idInfo(id);
          return this.$display_name(info?.display_name) || id;
        })
        .join(" & ");

      return this.$t("entire box with {names}", { names: displayNames });
    },

    removeExclude(divider, excludes, optionId, index) {
      if (excludes[index].length === 1) {
        const selectedId = excludes[index][0];
        this.selected_boops.forEach((box) => {
          box.ops.forEach((op) => {
            if (box.divider === divider || (!box.divider && !divider)) {
              if (op.id === selectedId) {
                op.excludes.forEach((excl, i) => {
                  if (excl[0] === optionId) {
                    op.excludes.splice(i, 1);
                  }
                });
              }
            }
          });
        });
        excludes.splice(index, 1);
      } else {
        const totalIds = [...excludes[index], optionId];
        this.selected_boops.forEach((box) => {
          box.ops.forEach((op) => {
            if (box.divider === divider || (!box.divider && !divider)) {
              op.excludes = op.excludes.filter((excl) => {
                return !excl.every((id) => totalIds.includes(id));
              });
            }
          });
        });
      }
    },
    checkState(id) {
      // Check if this option has any excludes (show indicator for any option with excludes)
      const optionHasExcludes =
        this.selected_boops &&
        this.selected_boops.some(
          (box) =>
            box.ops &&
            box.ops.some(
              (option) => option.id === id && option.excludes && option.excludes.length > 0,
            ),
        );

      // If there's a selected option, also check bidirectional relationship
      let bidirectionalExclude = false;
      if (this.selected_option && this.selected_option.id) {
        // Check if selected option excludes this option (A excludes B)
        const selectedExcludesThis =
          this.selected_option.excludes &&
          this.selected_option.excludes.findIndex(
            (x) => x.length > 0 && x.length < 2 && x.includes(id),
          ) >= 0;

        // Check if this option excludes the selected option (B excludes A)
        const thisExcludesSelected =
          this.selected_boops &&
          this.selected_boops.some(
            (box) =>
              box.ops &&
              box.ops.some(
                (option) =>
                  option.id === id &&
                  option.excludes &&
                  option.excludes.findIndex(
                    (x) => x.length > 0 && x.length < 2 && x.includes(this.selected_option.id),
                  ) >= 0,
              ),
          );

        bidirectionalExclude = selectedExcludesThis || thisExcludesSelected;
      }

      const result = optionHasExcludes || bidirectionalExclude;

      return result;
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
      for (let i = 0; i < this.selected_boops.length; i++) {
        const box = this.selected_boops[i];
        arr.push(box.ops);
      }
      this.flatOptions = [].concat(...arr);
    },

    generateManifest() {
      this.loading = true;
      this.api
        .post(`categories/${this.selected_category.slug}/boops`, {
          boops: this.selected_boops,
        })
        .then((response) => {
          this.set_generated_manifest(response);
          this.set_component("PrintProductManifest");
        });
    },
  },
};
</script>

<style lang="scss">
.trigger {
  display: flex !important;
}
</style>
