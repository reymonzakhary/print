<template>
  <div class="border-t">
    <div class="my-4 flex w-full">
      <UIInputText
        v-model="name"
        name="addDivider"
        placeholder="New divider"
        class="rounded-r-none"
      />
      <UIButton variant="theme" class="rounded-l-none rounded-r" @click="addDivider">Add</UIButton>
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
        Start by creating a new divider to organize your items. Enter a name above and click 'Add'.
      </p>
    </div>
    <div class="grid gap-2">
      <section
        v-for="(arr, divider, i) in groupedSpecs"
        :key="`divider_${i}`"
        class="rounded border-2 border-gray-300 p-2"
        :class="{
          'border-orange-400': divider === 'null' || divider === 'undefined' || divider === '',
        }"
      >
        <div
          class="group mb-2 flex cursor-pointer items-center justify-between text-sm font-bold uppercase text-gray-500"
        >
          {{
            divider === "null" || divider === "undefined" || divider === ""
              ? "not divided"
              : divider
          }}
          <section
            v-if="divider !== 'null' && divider !== 'undefined' && divider !== ''"
            class="flex items-center justify-end"
          >
            <span class="text-xs font-normal normal-case text-amber-500">
              <font-awesome-icon :icon="['fal', 'circle-exclamation']" />
              These lists are NOT sorted. Sorting is done on the main page.
            </span>
            <div class="invisible group-hover:visible">
              <button
                v-if="arr.length === 0 && divider !== 'null'"
                class="text-red-500 hover:text-red-700"
                @click="deleteDivider(divider)"
              >
                <font-awesome-icon :icon="['fal', 'trash']" />
              </button>
              <span v-else class="text-xs font-normal normal-case text-red-500">
                Can only delete when empty
              </span>
            </div>
          </section>
        </div>
        <draggable
          :model-value="arr"
          item-key="listId"
          group="dividers"
          ghost-class="ghost"
          :sort="false"
          class="h-full"
          @change="changeDivider($event, divider)"
        >
          <template #item="{ element: box }">
            <div
              class="group mb-1 flex cursor-pointer justify-between rounded bg-white p-2 shadow-md transition hover:shadow-lg dark:bg-gray-700"
            >
              {{ box.name }}
              <font-awesome-icon :icon="['fal', 'grip-lines']" />
            </div>
          </template>
        </draggable>
      </section>
    </div>
  </div>
</template>
<script setup>
import { v4 as uuidv4 } from "uuid";

const props = defineProps({
  selectedBoops: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:selectedBoops"]);
const { addToast } = useToastStore();

const boops = ref([...props.selectedBoops]);
const name = ref("");
const emptyGroups = ref([]);

const groupedSpecs = computed({
  get() {
    const groups = {
      null: [],
      ...emptyGroups.value.reduce((acc, groupName) => {
        acc[groupName] = [];
        return acc;
      }, {}),
    };

    boops.value.forEach((spec) => {
      spec.listId = uuidv4();
      const divider = spec.divider || "null";
      if (!groups[divider]) {
        groups[divider] = [];
      }
      groups[divider].push(spec);
    });

    return groups;
  },
  set(newGroupedSpecs) {
    boops.value = Object.values(newGroupedSpecs).flat();
    emit("update:selectedBoops", boops.value);
  },
});

const dividers = computed(() => Object.keys(groupedSpecs.value));
const groupArrays = computed(() => Object.values(groupedSpecs.value));

const addDivider = () => {
  if (!name.value) return;
  emptyGroups.value = [...emptyGroups.value, name.value];
  name.value = "";
};

const changeDivider = (e, divider) => {
  if (e.added) {
    const elementValue = e.added.element;
    const isDuplicate =
      groupedSpecs.value[divider].filter((spec) => spec.id === elementValue.id).length > 0;

    if (isDuplicate) {
      addToast({
        type: "warning",
        icon: "triangle-exclamation",
        message: "You cannot add two boxes of the same name to the same divider.",
      });
      return;
    }

    elementValue.divider = divider;
    const newGroupedSpecs = { ...groupedSpecs.value };
    const oldGroup =
      boops.value.find((spec) => spec.listId === elementValue.listId)?.divider || "null";

    if (newGroupedSpecs[oldGroup]) {
      const index = newGroupedSpecs[oldGroup].findIndex(
        (spec) => spec.listId === elementValue.listId,
      );
      if (index !== -1) {
        newGroupedSpecs[oldGroup].splice(index, 1);
      }
    }

    if (!newGroupedSpecs[divider]) {
      newGroupedSpecs[divider] = [];
    }
    newGroupedSpecs[divider].push(elementValue);

    groupedSpecs.value = newGroupedSpecs;

    if (emptyGroups.value.includes(divider)) {
      emptyGroups.value = emptyGroups.value.filter((group) => group !== divider);
    }
  }
};

const deleteDivider = (divider) => {
  if (groupedSpecs.value[divider]?.length === 0) {
    emptyGroups.value = emptyGroups.value.filter((group) => group !== divider);
  }
};
</script>

<style lang="scss" scoped>
.ghost {
  @apply rounded outline-dashed outline-1 outline-theme-500;
}
</style>
