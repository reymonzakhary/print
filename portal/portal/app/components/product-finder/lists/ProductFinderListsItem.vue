<template>
  <VDropdown placement="bottom" :disabled="editMode">
    <!-- List Item Button -->
    <button
      :class="[
        'drag-handle flex items-center rounded-full bg-gradient-to-br px-3 py-1.5 text-sm font-medium shadow-sm outline outline-1 transition-all',
        editMode ? 'cursor-move' : '',
        getColorClasses(list.color),
      ]"
    >
      <!-- Drag handle (visible only in edit mode) -->
      <font-awesome-icon
        v-if="editMode"
        :icon="['fal', 'grip-lines']"
        class="mr-2 h-3.5 w-3.5 cursor-move text-gray-400"
      />

      <span>{{ list.name }}</span>
      <span
        v-if="!editMode"
        class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs dark:bg-gray-600"
      >
        {{ list.amount }}
      </span>

      <!-- Delete button (visible only in edit mode) -->
      <button
        v-if="editMode"
        class="ml-2 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400"
        @click.stop="(event) => deleteWholeList(event)"
      >
        <font-awesome-icon :icon="['fal', 'times']" class="h-3.5 w-3.5" />
      </button>
    </button>

    <!-- Dropdown Content -->
    <template #popper>
      <ProductFinderListsDropdown :list="list" @update:list="$emit('update:list', $event)" />
    </template>
  </VDropdown>
</template>

<script setup>
const props = defineProps({
  list: {
    type: Object,
    required: true,
  },
  editMode: {
    type: Boolean,
    default: false,
  },
  index: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["delete", "update:list"]);

const { t: $t } = useI18n();

// Add this watcher to propagate list updates up the chain
watch(
  () => props.list,
  (newList) => {
    emit("update:list", newList);
  },
  { deep: true },
);

// Function to get color classes based on the color name
const getColorClasses = (color) => {
  const colorMap = {
    red: "from-red-50 to-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 outline-red-100 hover:from-red-100 hover:to-red-200 dark:hover:bg-red-900 dark:border-red-800",
    orange:
      "from-orange-50 to-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200 outline-orange-100 hover:from-orange-100 hover:to-orange-200 dark:hover:bg-orange-900 dark:border-orange-800",
    yellow:
      "from-yellow-50 to-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200 outline-yellow-100 hover:from-yellow-100 hover:to-yellow-200 dark:hover:bg-yellow-900 dark:border-yellow-800",
    green:
      "from-green-50 to-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 outline-green-100 hover:from-green-100 hover:to-green-200 dark:hover:bg-green-900 dark:border-green-800",
    teal: "from-teal-50 to-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-200 outline-teal-100 hover:from-teal-100 hover:to-teal-200 dark:hover:bg-teal-900 dark:border-teal-800",
    cyan: "from-cyan-50 to-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-200 outline-cyan-100 hover:from-cyan-100 hover:to-cyan-200 dark:hover:bg-cyan-900 dark:border-cyan-800",
    blue: "from-blue-50 to-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 outline-blue-100 hover:from-blue-100 hover:to-blue-200 dark:hover:bg-blue-900 dark:border-blue-800",
    indigo:
      "from-indigo-50 to-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 outline-indigo-100 hover:from-indigo-100 hover:to-indigo-200 dark:hover:bg-indigo-900 dark:border-indigo-800",
    purple:
      "from-purple-50 to-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200 outline-purple-100 hover:from-purple-100 hover:to-purple-200 dark:hover:bg-purple-900 dark:border-purple-800",
    pink: "from-pink-50 to-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-200 outline-pink-100 hover:from-pink-100 hover:to-pink-200 dark:hover:bg-pink-900 dark:border-pink-800",
    gray: "from-gray-50 to-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200 outline-gray-100 hover:from-gray-100 hover:to-gray-200 dark:hover:bg-gray-900 dark:border-gray-800",
  };
  return colorMap[color] || colorMap.gray;
};

const { confirm } = useConfirmation();
const deleteWholeList = (event) => {
  if (event && event.shiftKey) {
    // Skip confirmation if shift key is pressed
    emit("delete", props.index);
    return;
  }

  confirm({
    title: $t("Delete list"),
    message: $t("Are you sure you want to delete this list?"),
    confirmOptions: {
      label: $t("Delete"),
      variant: "danger",
    },
  }).then(() => {
    emit("delete", props.index);
  });
};
</script>
