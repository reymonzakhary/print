<template>
  <ProductFinderSection variant="primary" gradient class="flex h-[89px] w-full items-center p-0">
    <div class="flex w-full flex-col gap-4 p-3 md:flex-row md:items-end md:justify-between">
      <ProductFinderCompareToolbarGrouping
        v-model:group-by="groupBy"
        class="w-full"
        :grouping-options="groupingOptions"
        :disabled="comparisonActive"
      />
      <ProductFinderCompareToolbarSortMenu
        v-model="sortBy"
        :options="sortOptions"
        :disabled="validCombinationsCount === 0 || comparisonActive"
        :title="$t('Sort groups or variants:')"
      />
      <!-- <ProductFinderCompareToolbarMetricSelector v-model="metric" :options="metricOptions" /> -->
      <ProductFinderCompareToolbarCompareButton
        v-model="comparisonActive"
        :selected-count="selectedCount"
      />
    </div>
  </ProductFinderSection>
</template>

<script setup>
defineProps({
  activeGroup: {
    type: String,
    default: null,
  },
  groupingOptions: {
    type: Array,
    default: () => [],
  },
  sortOptions: {
    type: Array,
    default: () => [],
  },
  selectedCount: {
    type: Number,
    default: 0,
  },
  metricOptions: {
    type: Array,
    default: () => [],
  },
  validCombinationsCount: {
    type: Number,
    default: 0,
  },
});

const sortBy = defineModel("sortBy", { type: String, required: true });
const groupBy = defineModel("groupBy", { type: [Object, null], required: true });
const metric = defineModel("metric", { type: String, required: true });
const comparisonActive = defineModel("comparisonActive", { type: Boolean, required: true });
</script>
