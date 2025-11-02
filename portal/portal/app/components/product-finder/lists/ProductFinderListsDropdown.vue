<template>
  <div
    class="flex max-h-[50dvh] w-96 flex-col overflow-hidden rounded-lg bg-white p-4 shadow-lg dark:bg-gray-800"
  >
    <!-- Header -->
    <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ list.name }}
        </h3>
        <div class="flex items-center">
          <button
            class="ml-2 rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700"
            :title="'Change list color'"
            @click="showColorPicker = !showColorPicker"
          >
            <font-awesome-icon
              :icon="['fal', 'palette']"
              class="mb-0.5 mr-1 h-4 w-4 text-gray-500"
            />
          </button>
          <button
            v-close-popper
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          >
            <font-awesome-icon :icon="['fal', 'xmark']" class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Color picker (conditional) -->
    <div
      v-if="showColorPicker"
      class="mb-4 flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-700"
    >
      <button
        v-for="color in colorOptions"
        :key="color"
        :class="[
          'h-6 w-6 rounded-full',
          color === 'blue' ? 'bg-blue-500' : '',
          color === 'green' ? 'bg-green-500' : '',
          color === 'red' ? 'bg-red-500' : '',
          color === 'yellow' ? 'bg-yellow-500' : '',
          color === 'purple' ? 'bg-purple-500' : '',
          color === 'pink' ? 'bg-pink-500' : '',
          color === 'indigo' ? 'bg-indigo-500' : '',
          color === 'teal' ? 'bg-teal-500' : '',
          color === 'orange' ? 'bg-orange-500' : '',
          color === 'blue' && list.color === color ? 'ring-2 ring-blue-600 ring-offset-2' : '',
          color === 'green' && list.color === color ? 'ring-2 ring-green-600 ring-offset-2' : '',
          color === 'red' && list.color === color ? 'ring-2 ring-red-600 ring-offset-2' : '',
          color === 'yellow' && list.color === color ? 'ring-2 ring-yellow-600 ring-offset-2' : '',
          color === 'purple' && list.color === color ? 'ring-2 ring-purple-600 ring-offset-2' : '',
          color === 'pink' && list.color === color ? 'ring-2 ring-pink-600 ring-offset-2' : '',
          color === 'indigo' && list.color === color ? 'ring-2 ring-indigo-600 ring-offset-2' : '',
          color === 'teal' && list.color === color ? 'ring-2 ring-teal-600 ring-offset-2' : '',
          color === 'orange' && list.color === color ? 'ring-2 ring-orange-600 ring-offset-2' : '',
          color === 'blue' ? 'hover:ring-2 hover:ring-blue-300' : '',
          color === 'green' ? 'hover:ring-2 hover:ring-green-300' : '',
          color === 'red' ? 'hover:ring-2 hover:ring-red-300' : '',
          color === 'yellow' ? 'hover:ring-2 hover:ring-yellow-300' : '',
          color === 'purple' ? 'hover:ring-2 hover:ring-purple-300' : '',
          color === 'pink' ? 'hover:ring-2 hover:ring-pink-300' : '',
          color === 'indigo' ? 'hover:ring-2 hover:ring-indigo-300' : '',
          color === 'teal' ? 'hover:ring-2 hover:ring-teal-300' : '',
          color === 'orange' ? 'hover:ring-2 hover:ring-orange-300' : '',
        ]"
        @click="updateListColor(color)"
      />
      <button
        :class="[
          'h-6 w-6 rounded-full bg-gray-200 hover:ring-2 hover:ring-gray-300 dark:bg-gray-700',
          !list.color ? 'ring-2 ring-gray-600 ring-offset-2' : '',
        ]"
        @click="updateListColor(null)"
      >
        <font-awesome-icon :icon="['fal', 'xmark']" class="h-3.5 w-3.5 text-gray-500" />
      </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="$t('Search in categories') + '...'"
          class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 pl-10 text-sm focus:border-theme-500 focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200"
        />
        <font-awesome-icon
          :icon="['fal', 'search']"
          class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
        />
      </div>
    </div>

    <!-- Categories List -->
    <ul class="flex-1 space-y-2 overflow-y-auto">
      <li v-for="category in filteredCategories" :key="category.name" class="">
        <NuxtLink
          :to="`/marketplace/product-finder/${category.slug}`"
          class="group flex cursor-pointer items-center justify-between rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          <div class="flex items-center space-x-3">
            <div
              class="grid size-10 flex-shrink-0 place-items-center overflow-hidden rounded-lg bg-gradient-to-br from-theme-50 to-theme-200 p-1 dark:from-theme-300"
            >
              <NuxtImg v-if="category.image" :src="category.image" class="size-6" />
              <font-awesome-icon v-else :icon="['fal', 'image']" class="size-6 text-theme-500" />
            </div>
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ category.name }}</span>
          </div>
          <button
            class="invisible text-gray-400 hover:text-red-500 group-hover:visible dark:text-gray-500 dark:hover:text-red-400"
            @click.prevent.stop="removeCategory(category.id, $event)"
          >
            <font-awesome-icon :icon="['fal', 'trash']" class="h-4 w-4" />
          </button>
        </NuxtLink>
      </li>

      <li v-if="filteredCategories.length === 0" class="py-4 text-center text-sm text-gray-500">
        {{ $t("No categories found in this list") }}
      </li>
    </ul>

    <!-- Footer -->
    <div v-if="filteredCategories.length > 0" class="flex items-center pt-2">
      <font-awesome-icon :icon="['fal', 'info']" class="h-3 w-3" />
      <span class="mr-3 text-xs text-gray-400 dark:text-gray-500">
        {{ $t("Hold") }}
        <kbd
          class="mx-0.5 rounded border border-gray-300 bg-gray-100 px-1 py-0.5 dark:border-gray-700 dark:bg-gray-800"
          >Shift</kbd
        >
        {{ $t("to delete without confirmation") }}
      </span>
    </div>
    <div class="mt-4 flex justify-end border-t border-gray-200 pt-3 dark:border-gray-700">
      <button
        class="rounded-lg bg-theme-500 px-4 py-2 text-sm font-medium text-white hover:bg-theme-600 dark:bg-theme-600 dark:hover:bg-theme-700"
        @click="showAddCategoryModal = true"
      >
        {{ $t("Add new category") }}
      </button>
    </div>

    <!-- Add Category Modal -->
    <ProductFinderListsModalCategory
      v-if="showAddCategoryModal"
      :list="list"
      :available-categories="availableCategories"
      @close="showAddCategoryModal = false"
      @add="addCategories"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  list: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "update:list"]);

const { t: $t } = useI18n();

const productFinderStore = useProductFinderStore();

const searchQuery = ref("");
const showColorPicker = ref(false);
const showAddCategoryModal = ref(false);

// Import useConfirmation
const { confirm } = useConfirmation();

// Get all available categories from the parent component
const allCategories = productFinderStore.categories;

// Filter out categories that could be added to the list
const availableCategories = computed(() => {
  // If list has no categories yet, return all categories
  if (!props.list.categories || props.list.categories.length === 0) {
    return allCategories;
  }

  // Get IDs of categories already in the list
  const existingCategoryIds = props.list.categories.map((cat) => cat.id);

  // Return categories not already in the list
  return allCategories.filter((cat) => !existingCategoryIds.includes(cat.id));
});

// List of available color options
const colorOptions = [
  "blue",
  "green",
  "red",
  "yellow",
  "purple",
  "pink",
  "indigo",
  "teal",
  "orange",
];

// Filtered categories based on search
const realCategories = computed(() => {
  const categories = props.list.categories || [];
  return categories.map((cat) => allCategories.find((c) => c.id === cat.id));
});

const filteredCategories = computed(() => {
  if (!searchQuery.value) return realCategories.value;
  return realCategories.value.filter((cat) =>
    cat.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
  );
});

// Update list color
const updateListColor = (color) => {
  const updatedList = { ...props.list, color };
  emit("update:list", updatedList);
  showColorPicker.value = false;
};

// Remove category from list
const removeCategory = async (categoryId, event) => {
  // Prevent event propagation that could close the dropdown
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  // Check if shift key is pressed
  if (event && event.shiftKey) {
    performDeletion(categoryId);
    return;
  }

  // Ask for confirmation
  try {
    await confirm({
      title: $t("Confirm Deletion"),
      message: $t("Are you sure you want to remove this category from the list?"),
      confirmOptions: {
        okLabel: $t("Delete"),
        cancelLabel: $t("Cancel"),
      },
    });

    // If confirmed, delete
    performDeletion(categoryId);
  } catch {
    // User cancelled, do nothing
  }
};

// Extract the actual deletion logic to avoid repetition
const performDeletion = (categoryId) => {
  const updatedCategories = props.list.categories.filter((cat) => cat.id !== categoryId);
  const updatedList = {
    ...props.list,
    categories: updatedCategories,
    amount: updatedCategories.length,
  };

  emit("update:list", updatedList);
};

// Add multiple categories to list
const addCategories = (categories) => {
  // Combine existing categories with new ones
  const updatedCategories = [...(props.list.categories || []), ...categories];

  const updatedList = {
    ...props.list,
    categories: updatedCategories,
    amount: updatedCategories.length,
  };

  emit("update:list", updatedList);
  showAddCategoryModal.value = false;
};
</script>
