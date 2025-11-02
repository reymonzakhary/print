<template>
  <div>
    <div
      v-if="categories.length === 0"
      class="mt-10 flex h-64 w-full flex-col items-center justify-center"
    >
      <SkeletonLine v-if="loading" class="!h-6 w-48" />
      <template v-else>
        <font-awesome-icon
          :icon="['far', 'frown']"
          class="mb-2 text-4xl text-gray-300 dark:text-gray-600"
        />
        <p class="text-gray-500 dark:text-gray-400">{{ $t("No categories found") }}</p>
      </template>
    </div>
    <div v-else class="grid grid-cols-2 gap-5 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
      <ProductFinderCategoryCard
        v-for="category in categories"
        :key="category.id"
        :category="category"
        @order="handleCategoryOrder"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const selectedCategory = ref(null);

const handleCategoryOrder = (category) => {
  selectedCategory.value = category;
  const productFinderStore = useProductFinderStore();
  productFinderStore.openBasketDialog(true, null, category);
};
</script>
