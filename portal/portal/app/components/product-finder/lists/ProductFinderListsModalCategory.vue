<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div
      class="max-h-[80vh] w-full max-w-md overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800"
    >
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $t("Add category to list") }}
          </h3>
          <button
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            @click="$emit('close')"
          >
            <font-awesome-icon :icon="['fal', 'xmark']" class="h-5 w-5" />
          </button>
        </div>
      </div>

      <!-- Search -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="$t('Search categories') + '...'"
            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 pl-10 text-sm focus:border-theme-500 focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200"
          />
          <font-awesome-icon
            :icon="['fal', 'search']"
            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
          />
        </div>
      </div>

      <!-- Categories List -->
      <div class="max-h-[40vh] overflow-y-auto p-6">
        <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <li
            v-for="category in filteredCategories"
            :key="category.id"
            class="flex cursor-pointer items-start space-x-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
            :class="{
              'ring-2 ring-theme-500 dark:ring-theme-400': selectedCategories.includes(category.id),
            }"
            @click="toggleCategorySelection(category.id)"
          >
            <div
              class="grid size-10 flex-shrink-0 place-items-center overflow-hidden rounded-lg bg-gradient-to-br from-theme-50 to-theme-200 p-1 dark:from-theme-300"
            >
              <NuxtImg v-if="category.image" :src="category.image" class="size-6" />
              <font-awesome-icon v-else :icon="['fal', 'image']" class="size-6 text-theme-500" />
            </div>
            <div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ category.name }}
              </h4>
              <p v-if="category.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ category.description }}
              </p>
            </div>
          </li>

          <li
            v-if="filteredCategories.length === 0"
            class="col-span-2 py-8 text-center text-sm text-gray-500"
          >
            {{ $t("No categories found with this search") }}
          </li>
        </ul>
      </div>

      <!-- Footer -->
      <div
        class="flex justify-end border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800"
      >
        <button
          class="mr-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
          @click="$emit('close')"
        >
          {{ $t("Cancel") }}
        </button>
        <button
          class="rounded-lg bg-theme-500 px-4 py-2 text-sm font-medium text-white hover:bg-theme-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-theme-600 dark:hover:bg-theme-700"
          :disabled="selectedCategories.length === 0"
          @click="addSelectedCategories"
        >
          {{ selectedCategories.length }} {{ $t("categories to add") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  list: {
    type: Object,
    required: true,
  },
  availableCategories: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["close", "add"]);

const searchQuery = ref("");
const selectedCategories = ref([]);

// Filter categories that are not already in the list and match search query
const filteredCategories = computed(() => {
  // Get IDs of categories already in the list
  const existingCategoryIds = (props.list.categories || []).map((cat) => cat.id);

  // Filter available categories to exclude ones already in the list
  let categories = props.availableCategories.filter((cat) => !existingCategoryIds.includes(cat.id));

  // Apply search filter if query exists
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    categories = categories.filter(
      (cat) =>
        cat.name.toLowerCase().includes(query) ||
        (cat.description && cat.description.toLowerCase().includes(query)),
    );
  }

  return categories;
});

// Toggle category selection
const toggleCategorySelection = (categoryId) => {
  const index = selectedCategories.value.findIndex((id) => id === categoryId);
  if (index === -1) {
    selectedCategories.value.push(categoryId);
  } else {
    selectedCategories.value.splice(index, 1);
  }
};

// Add selected categories to the list
const addSelectedCategories = () => {
  const categoriesToAdd = props.availableCategories.filter((cat) =>
    selectedCategories.value.includes(cat.id),
  );
  emit("add", categoriesToAdd);
};
</script>
