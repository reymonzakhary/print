<template>
  <div class="border-t">
    <div class="my-4 flex w-full justify-between">
      <div class="flex">
        <UIInputText
          v-model="name"
          name="addDivider"
          :placeholder="$t('new divider')"
          class="rounded-r-none"
        />
        <UIButton variant="theme" class="rounded-l-none rounded-r" @click="addDivider"
          >Add</UIButton
        >
      </div>
      <div>
        <span class="text-xs font-normal normal-case text-amber-500">
          <font-awesome-icon :icon="['fal', 'circle-exclamation']" />
          {{ $t("Drag to sort within groups or move between dividers.") }}
        </span>
      </div>
    </div>

    <!-- Help message when only null divider exists -->
    <div
      v-if="
        Object.keys(groupedSpecs).length === 1 &&
        (groupedSpecs['null'] || groupedSpecs['undefined'] || groupedSpecs[''])
      "
      class="mx-auto my-4 w-full rounded-md border border-blue-200 bg-blue-50 p-4 text-center"
    >
      <p class="text-blue-600">
        {{
          $t(
            "Start by creating a new divider to organize your items. Enter a name above and click 'Add'.",
          )
        }}
      </p>
    </div>

    <div class="grid gap-2">
      <section
        v-for="(arr, divider, i) in localGroupedSpecs"
        :key="`divider_${i}`"
        class="rounded border-2 border-gray-300 p-2"
        :class="
          divider !== 'null' && divider !== 'undefined' && divider !== ''
            ? `mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-800`
            : 'border-orange-400'
        "
      >
        <div class="mb-4 flex items-center justify-between">
          <div
            class="flex items-center gap-2 text-lg font-semibold uppercase text-gray-700 dark:text-gray-300"
          >
            <font-awesome-icon :icon="['fal', 'layer-group']" class="mr-2" />
            <span v-if="divider === 'null' || divider === 'undefined' || divider === ''">
              {{ $t("not divided") }}
            </span>
            <UIInputText
              v-else
              :model-value="divider"
              name="EditDivider"
              :placeholder="$t('edit divider')"
              class="rounded-r-none"
              @blur="(e) => renameDivider(divider, e)"
            />
          </div>
          <div class="flex gap-3">
            <div
              class="group mb-2 flex cursor-pointer items-center justify-between text-sm font-bold uppercase text-gray-500"
            >
              <section
                v-if="divider !== 'null' && divider !== 'undefined' && divider !== ''"
                class="flex items-center justify-end"
              >
                <div class="invisible group-hover:visible">
                  <button
                    v-if="arr.length === 0 && divider !== 'null'"
                    class="text-red-500 hover:text-red-700"
                    @click="deleteDivider(divider)"
                  >
                    <font-awesome-icon :icon="['fal', 'trash']" />
                  </button>
                  <span v-else class="text-xs font-normal normal-case text-red-500">
                    {{ $t("can only delete when empty") }}
                  </span>
                </div>
              </section>
            </div>
            <span class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-600">
              {{ arr.length }} {{ arr.length === 1 ? "box" : "boxes" }}
            </span>
          </div>
        </div>

        <!-- Fixed draggable with proper v-model and reactive update -->
        <draggable
          v-model="localGroupedSpecs[divider]"
          item-key="id"
          group="dividers"
          ghost-class="ghost"
          class="min-h-[60px] space-y-2 rounded border-2 border-dashed border-gray-300 p-3 dark:border-gray-600"
          :animation="200"
          @start="drag = true"
          @end="onDragEnd"
          @change="onGroupChange($event, divider)"
        >
          <template #item="{ element: box }">
            <div
              class="group mb-1 flex cursor-pointer justify-between rounded bg-white p-2 shadow-md transition hover:shadow-lg dark:bg-gray-700"
            >
              {{ $display_name(box.display_name) }}
              <div class="flex items-center gap-3">
                <span class="text-gray-500">
                  {{ box.system_key }}
                </span>
                <font-awesome-icon :icon="['fal', 'grip-lines']" />
              </div>
            </div>
          </template>
        </draggable>
      </section>
    </div>
  </div>
</template>

<script>
import { v4 as uuidv4 } from "uuid";
import { mapMutations } from "vuex";
import _ from "lodash";

export default {
  props: {
    selectedBoops: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["update:selectedBoops"],
  setup() {
    const { addToast } = useToastStore();
    return { addToast };
  },
  data() {
    return {
      boops: [...this.selectedBoops],
      name: "",
      drag: false,
      emptyGroups: [],
      localGroupedSpecs: {}, // This will hold our reactive grouped data
    };
  },
  computed: {
    // This is now only used for reading, not for v-model
    groupedSpecs() {
      const groups = {
        null: [],
        ...this.emptyGroups.reduce((acc, groupName) => {
          acc[groupName] = [];
          return acc;
        }, {}),
      };

      this.boops.forEach((spec) => {
        // Ensure each item has required properties
        if (!spec.id && !spec.listId) {
          spec.listId = uuidv4();
        }
        const divider = spec.divider || "null";
        if (!groups[divider]) {
          groups[divider] = [];
        }
        groups[divider].push(spec);
      });

      return groups;
    },
  },
  watch: {
    selectedBoops: {
      handler(newVal) {
        this.boops = [...newVal];
        this.updateLocalGroupedSpecs();
      },
      deep: true,
      immediate: true,
    },

    groupedSpecs: {
      handler() {
        this.updateLocalGroupedSpecs();
      },
      deep: true,
    },
  },
  methods: {
    ...mapMutations({
      set_selected_boops: "product_wizard/set_selected_boops",
    }),
    renameDivider(oldKey, event) {
      const value = (event?.target?.value ?? "").trim();
      if (!value || ["null", "undefined"].includes(value)) {
        this.addToast({
          type: "warning",
          icon: "triangle-exclamation",
          message: this.$t("Invalid divider name"),
        });
        event.target.value = oldKey;
        return;
      }
      if (value === oldKey) return;
      if (this.localGroupedSpecs[value]) {
        this.addToast({
          type: "warning",
          icon: "triangle-exclamation",
          message: this.$t("Divider already exists"),
        });
        event.target.value = oldKey;
        return;
      }
      // move items and update emptyGroups
      this.localGroupedSpecs[value] = this.localGroupedSpecs[oldKey] || [];
      delete this.localGroupedSpecs[oldKey];
      this.emptyGroups = this.emptyGroups.filter((g) => g !== oldKey);
      if ((this.localGroupedSpecs[value] || []).length === 0) {
        this.emptyGroups = [...this.emptyGroups, value];
      }
      this.syncToMainData();
    },
    updateLocalGroupedSpecs() {
      // Create reactive copy from computed groupedSpecs
      this.localGroupedSpecs = {};
      Object.entries(this.groupedSpecs).forEach(([divider, items]) => {
        this.localGroupedSpecs[divider] = [...items];
      });
    },

    syncToMainData() {
      // Flatten local grouped specs back to boops array and emit
      const flattenedBoops = [];
      Object.entries(this.localGroupedSpecs).forEach(([divider, items]) => {
        items.forEach((item) => {
          // Update divider property
          item.divider = divider === "null" ? null : divider;
          flattenedBoops.push(item);
        });
      });

      this.boops = flattenedBoops;
      this.set_selected_boops(_.cloneDeep(flattenedBoops));
      this.$emit("update:selectedBoops", flattenedBoops);
    },

    onGroupChange(evt, targetDivider) {
      if (evt.added) {
        const elementValue = evt.added.element;

        // Check for duplicates
        const existingItems = this.localGroupedSpecs[targetDivider] || [];
        const isDuplicate = existingItems.filter((spec) => spec.id === elementValue.id).length > 1;

        if (isDuplicate) {
          this.addToast({
            type: "warning",
            icon: "triangle-exclamation",
            message: this.$t("You cannot add two boxes of the same name to the same divider."),
          });
          return;
        }

        // Update divider property
        elementValue.divider = targetDivider === "null" ? null : targetDivider;

        // Remove from empty groups if item was added
        if (this.emptyGroups.includes(targetDivider)) {
          this.emptyGroups = this.emptyGroups.filter((group) => group !== targetDivider);
        }
      }

      // Sync changes back to main data
      this.syncToMainData();
    },

    addDivider() {
      if (!this.name) return;

      // Check if divider already exists
      if (this.localGroupedSpecs[this.name]) {
        this.addToast({
          type: "warning",
          message: this.$t("Divider already exists"),
        });
        return;
      }

      // Add to empty groups list
      this.emptyGroups = [...this.emptyGroups, this.name];

      // Also add to local grouped specs
      this.localGroupedSpecs[this.name] = [];

      this.name = "";
    },

    onDragEnd() {
      this.drag = false;
      // Ensure data is synced after drag ends
      this.syncToMainData();
    },

    deleteDivider(divider) {
      // Only allow deletion if the group is empty
      if (this.groupedSpecs[divider]?.length === 0) {
        // Remove from empty groups list
        this.emptyGroups = this.emptyGroups.filter((group) => group !== divider);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.ghost {
  @apply rounded opacity-50 outline-dashed outline-1 outline-theme-500;
  background: rgba(59, 130, 246, 0.1);
}
</style>
