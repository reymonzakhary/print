<template>
  <div class="mx-auto flex max-w-5xl flex-col space-y-6 py-2">
    <!-- Number of Results Section -->
    <div
      class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
    >
      <label
        class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
      >
        <font-awesome-icon :icon="['fal', 'hashtag']" class="text-theme-500" />
        {{ $t("number of results") }}
      </label>
      <div class="flex flex-col gap-3 md:flex-row md:items-start">
        <input
          v-model.number="rangeAround"
          type="number"
          class="w-32 rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-800 shadow-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        />
        <div class="flex-1 rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
          <div class="flex items-start gap-2 text-sm text-blue-800 dark:text-blue-200">
            <font-awesome-icon :icon="['fal', 'circle-info']" class="mt-0.5 flex-shrink-0" />
            <div class="space-y-1">
              <p>
                {{
                  $t(
                    "number of results returned in the pricelist before and after de given quantity (in free entry).",
                  )
                }}
              </p>
              <p class="text-xs italic">
                {{
                  $t(
                    "for example, if the range is 10, and the range around is 5, the pricelist will show 5 before and 5 after the given quantity.",
                  )
                }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Range Section -->
    <div
      class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
    >
      <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
        <font-awesome-icon :icon="['fal', 'plus-circle']" class="text-theme-500" />
        {{ $t("add new range for a printing method") }}
      </h2>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <select
          v-model="newGroupName"
          class="input flex-1 rounded-md border border-gray-300 px-4 py-2 shadow-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
          <option value="" disabled>{{ $t("Select a printing method") }}</option>
          <option v-for="name in availableGroupNames" :key="name" :value="name">
            {{ name }}
          </option>
        </select>
        <UIButton
          class="dark:hover:bg-theme-900/20 rounded-md border border-theme-500 px-6 py-2 text-sm font-medium text-theme-500 transition hover:bg-theme-50 disabled:opacity-50"
          :disabled="!newGroupName"
          @click="addNewGroup"
        >
          <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
          {{ $t("add new range") }}
        </UIButton>
      </div>
    </div>

    <!-- <div class="p-4">
      <label class="block mb-2 text-xs font-bold tracking-widest uppercase">
        {{ $t("what ranges to display as a table in your webshop") }}
      </label>
      <input
        v-for="i in 10"
        :key="i"
        v-model.number="rangeList[i - 1]"
        type="number"
        class="w-24 px-2 py-1 border rounded"
      />
    </div> -->

    <!-- Ranges Section -->
    <section class="flex flex-col space-y-6">
      <div
        v-for="(group, name) in groupedRanges"
        :key="name"
        class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
      >
        <div
          class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700"
        >
          <h2 class="flex items-center gap-3 text-lg font-bold text-gray-900 dark:text-white">
            <div
              class="flex h-8 w-8 items-center justify-center rounded-md bg-theme-100 dark:bg-theme-900"
            >
              <font-awesome-icon
                :icon="['fal', 'print']"
                class="text-theme-600 dark:text-theme-400"
              />
            </div>
            <span>{{ name }}</span>
          </h2>
          <span
            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-400"
          >
            {{ group.length }} {{ group.length === 1 ? $t("range") : $t("ranges") }}
          </span>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
          <!-- Quantity Ranges Card -->
          <article
            class="rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"
          >
            <header
              class="flex items-center justify-between rounded-t-md border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
            >
              <div class="flex flex-1 items-center gap-6">
                <div class="flex min-w-0 flex-1 items-center gap-2">
                  <font-awesome-icon
                    class="flex-shrink-0 text-gray-400"
                    :icon="['fal', 'bow-arrow']"
                  />
                  <span
                    class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300"
                    >{{ $t("run") }}</span
                  >
                </div>
                <div class="flex w-20 items-center gap-2">
                  <font-awesome-icon
                    class="flex-shrink-0 text-gray-400"
                    :icon="['fal', 'arrows-h']"
                  />
                  <span
                    class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300"
                    >{{ $t("interval") }}</span
                  >
                </div>
              </div>
              <div class="w-16 text-right">
                <span
                  class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300"
                  >{{ $t("actions") }}</span
                >
              </div>
            </header>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="(range, index) in group"
                :key="index"
                class="flex items-center gap-3 p-3 transition-colors hover:bg-white dark:hover:bg-gray-800"
              >
                <div class="flex flex-1 items-center gap-2">
                  <input
                    v-model.number="range.from"
                    type="number"
                    class="input w-20 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                  <span class="text-xs font-medium text-gray-400 dark:text-gray-500">→</span>
                  <input
                    v-model.number="range.to"
                    type="number"
                    class="input w-20 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    :min="range.from + 1"
                    @input="updateFrom(index)"
                  />
                </div>
                <div class="w-20">
                  <input
                    v-model.number="range.incremental_by"
                    type="number"
                    class="input w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
                <button
                  class="flex h-8 w-8 items-center justify-center rounded-md text-red-600 transition hover:bg-red-100 dark:hover:bg-red-900/30"
                  @click="removeRange(name, index)"
                  :title="$t('Remove range')"
                >
                  <font-awesome-icon :icon="['fal', 'trash']" />
                </button>
              </div>
            </div>
            <div
              class="rounded-b-md border-t border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800"
            >
              <button
                class="dark:hover:bg-theme-900/20 flex w-full items-center justify-center gap-2 rounded-md border-2 border-dashed border-gray-300 px-4 py-3 text-sm font-medium text-gray-600 transition hover:border-theme-500 hover:bg-theme-50 hover:text-theme-600 dark:border-gray-600 dark:text-gray-400 dark:hover:border-theme-500 dark:hover:text-theme-400"
                @click="addRange(name)"
              >
                <font-awesome-icon :icon="['fal', 'plus']" />
                {{ $t("add quantity range") }}
              </button>
            </div>
          </article>

          <!-- Free Entry and Limits Section -->
          <article
            class="rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"
          >
            <header
              class="rounded-t-md border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
            >
              <h3
                class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300"
              >
                <font-awesome-icon :icon="['fal', 'sliders-h']" class="text-gray-400" />
                {{ $t("free entry and limits") }}
              </h3>
            </header>

            <div class="space-y-4 p-4">
              <!-- Free Entry Toggle -->
              <div class="rounded-md bg-white p-4 dark:bg-gray-800">
                <label class="flex cursor-pointer items-center gap-3">
                  <input
                    v-model="getFreeEntry(name).enable"
                    type="checkbox"
                    class="h-5 w-5 rounded border-gray-300 text-theme-500 transition focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700"
                    @change="updateFreeEntry(name)"
                  />
                  <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                      $t("enable free entry")
                    }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ $t("Allow customers to enter custom quantities") }}
                    </p>
                  </div>
                </label>
              </div>

              <!-- Free Entry Interval -->
              <div
                v-if="getFreeEntry(name).enable"
                class="rounded-md bg-white p-4 dark:bg-gray-800"
              >
                <label
                  class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                >
                  <font-awesome-icon :icon="['fal', 'repeat']" class="text-theme-500" />
                  {{ $t("free entry interval") }}
                </label>
                <input
                  v-model.number="getFreeEntry(name).interval"
                  type="number"
                  class="mb-3 w-32 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  @input="updateFreeEntry(name)"
                />
                <div class="rounded-md bg-blue-50 p-3 dark:bg-blue-900/20">
                  <div class="flex items-start gap-2 text-xs text-blue-800 dark:text-blue-200">
                    <font-awesome-icon
                      :icon="['fal', 'circle-info']"
                      class="mt-0.5 flex-shrink-0"
                    />
                    <p>
                      {{
                        $t(
                          "This number will override the interval of the range. If the range is 10, and the free entry interval is 5, the range will be 5",
                        )
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Ceiling Limit -->
              <div class="rounded-md bg-white p-4 dark:bg-gray-800">
                <label
                  class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                >
                  <font-awesome-icon :icon="['fal', 'layer-group']" class="text-theme-500" />
                  {{ $t("ceiling limit") }}
                </label>
                <input
                  v-model.number="getLimit(name).ceiling"
                  type="number"
                  class="mb-3 w-32 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm transition focus:border-theme-500 focus:ring-2 focus:ring-theme-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  @input="updateLimit(name)"
                />
                <div class="rounded-md bg-amber-50 p-3 dark:bg-amber-900/20">
                  <div class="flex items-start gap-2 text-xs text-amber-800 dark:text-amber-200">
                    <font-awesome-icon
                      :icon="['fal', 'triangle-exclamation']"
                      class="mt-0.5 flex-shrink-0"
                    />
                    <p>
                      {{
                        $t(
                          "number of results returned in the pricelist (these dynamic ranges can be quite long). Make sure to set a limit to prevent performance issues. Max result is always 30 ranges.",
                        )
                      }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- <div
      class="sticky bottom-0 mt-2 flex justify-center rounded bg-white p-2 shadow-md dark:bg-gray-700"
    > -->
    <UIButton
      v-if="!wizardMode"
      class="mx-1 self-end rounded-full px-4 py-1 !text-base"
      variant="success"
      @click="$emit('onUpdateRanges', { ranges, limits, freeEntry, rangeAround, rangeList })"
    >
      <font-awesome-icon :icon="['fal', 'save']" class="mr-2" />
      {{ $t("Save price ranges") }}
    </UIButton>
    <!-- </div> -->
  </div>
</template>

<script>
import { debounce } from "lodash";
import { mapState, mapActions } from "vuex";
export default {
  name: "CategoryRanges",
  props: {
    categoryRanges: {
      type: Array,
      default: () => [],
    },
    categoryRangeList: {
      type: Array,
      default: () => [],
    },
    categoryLimits: {
      type: Array,
      default: () => [],
    },
    categoryFreeEntry: {
      type: Array,
      default: () => [],
    },
    categoryRangeAround: {
      type: Number,
      default: 10,
    },
    wizardMode: {
      type: Boolean,
      default: false,
    },
    // selectedCategory: {
    //   type: Object,
    //   default: () => [],
    // },
  },
  emits: ["onUpdateRanges"],
  data() {
    return {
      // ranges: [
      //   { name: "digital", slug: "digital", from: 0, to: 50, incremental_by: 10 },
      //   { name: "digital", slug: "digital", from: 51, to: 250, incremental_by: 50 },
      //   { name: "digital", slug: "digital", from: 251, to: 1000, incremental_by: 250 },
      //   { name: "offset", slug: "offset", from: 1001, to: 100000, incremental_by: 1000 },
      // ],
      ranges: [...this.categoryRanges],
      rangeList: [...this.categoryRangeList],
      limits: [...this.categoryLimits],
      freeEntry: [...this.categoryFreeEntry],
      rangeAround: this.categoryRangeAround ?? 10,
      // allGroupNames: ["Digital", "Offset", "Letterpress", "Screen Printing", "Engraving"],
      newGroupName: "",
    };
  },
  computed: {
    groupedRanges() {
      const groups = {};
      this.ranges.forEach((range) => {
        const name = range.name.toLowerCase(); // Convert name to lowercase
        if (!groups[name]) {
          groups[name] = [];
        }
        groups[name].push(range);
      });
      return groups;
    },

    ...mapState({
      // vuex product
      printing_methods: (state) => state.printing_methods.printing_methods,
    }),
    allGroupNames() {
      return this.printing_methods.map((method) => method.name); // Convert names to lowercase
    },
    availableGroupNames() {
      const usedNames = new Set(this.ranges.map((range) => range.name)); // Convert names to lowercase
      return this.allGroupNames.filter((name) => !usedNames.has(name));
    },
  },
  watch: {
    groupedRanges: {
      handler: debounce(function (newGroupedRanges) {
        const newRanges = [];
        for (const name in newGroupedRanges) {
          newRanges.push(...newGroupedRanges[name]);
        }
        for (let i = 1; i < newRanges.length; i++) {
          if (
            newRanges[i].name === newRanges[i - 1].name &&
            newRanges[i].from <= newRanges[i - 1].to
          ) {
            newRanges[i].from = newRanges[i - 1].to + 1;
          }
        }
      }, 1000),
      deep: true,
    },
  },
  created() {
    this.getPrintingMethods();
  },
  methods: {
    ...mapActions({
      // vuex product
      getPrintingMethods: "printing_methods/get_printing_methods",
    }),
    addRange(name) {
      const lastRange = this.ranges.filter((r) => r.name.toLowerCase() === name).pop();
      const newRange = {
        name,
        slug: name.toLowerCase(),
        from: lastRange ? lastRange.to + 1 : 0,
        to: lastRange ? lastRange.to + 100 : 1000,
        incremental_by: 10,
      };
      this.ranges.push(newRange);
    },
    removeRange(name, index) {
      // Find all indices of ranges with the given name
      const groupIndices = this.ranges.reduce((acc, range, i) => {
        if (range.name.toLowerCase() === name) acc.push(i);
        return acc;
      }, []);

      if (groupIndices.length === 0) {
        console.error(`No group found with name: ${name}`);
        return;
      }

      // Check if the index is valid
      if (index < 0 || index >= groupIndices.length) {
        console.error(`Invalid index ${index} for group ${name}`);
        return;
      }

      // Remove the range at the calculated position
      const positionToRemove = groupIndices[index];
      this.ranges.splice(positionToRemove, 1);
    },
    updateFrom(index) {
      if (index >= 1 && this.ranges[index].from <= this.ranges[index - 1].to) {
        this.ranges[index].from = this.ranges[index - 1].to + 1;
      }
    },
    addNewGroup() {
      if (this.newGroupName && this.availableGroupNames.includes(this.newGroupName)) {
        const newRange = {
          name: this.newGroupName,
          slug: this.newGroupName.toLowerCase().replace(/\s+/g, "-"),
          from: 0,
          to: 1000,
          incremental_by: 10,
        };
        this.ranges.push(newRange);
        this.newGroupName = ""; // Reset the select after adding
      }
    },
    getFreeEntry(name) {
      const slug = this.getSlugFromName(name);
      let entry = this.freeEntry.find((e) => e.slug === slug);
      if (!entry) {
        entry = { slug, enable: false, interval: 10 };
        this.freeEntry.push(entry);
      }
      return entry;
    },

    getLimit(name) {
      const slug = this.getSlugFromName(name);
      let limit = this.limits.find((l) => l.slug === slug);
      if (!limit) {
        limit = { slug, ceiling: 10 };
        this.limits.push(limit);
      }
      return limit;
    },

    getSlugFromName(name) {
      return name.toLowerCase().replace(/\s+/g, "-");
    },

    updateFreeEntry(name) {
      const entry = this.getFreeEntry(name);
      const index = this.freeEntry.findIndex((e) => e.slug === entry.slug);
      if (index !== -1) {
        this.freeEntry.splice(index, 1, { ...entry });
      } else {
        this.freeEntry.push(entry);
      }
    },

    updateLimit(name) {
      const limit = this.getLimit(name);
      const index = this.limits.findIndex((l) => l.slug === limit.slug);
      if (index !== -1) {
        this.$set(this.limits, index, { ...limit });
      } else {
        this.limits.push(limit);
      }
    },
  },
};
</script>
