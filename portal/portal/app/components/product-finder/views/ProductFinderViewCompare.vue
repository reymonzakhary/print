<template>
  <section>
    <ProductFinderUIBackground />
    <div class="relative space-y-4">
      <ProductFinderCompareToolbar
        v-model:sort-by="sortBy"
        v-model:group-by="groupBy"
        v-model:metric="metric"
        v-model:comparison-active="comparisonActive"
        :class="cn('sticky top-8 z-40', activeSales && 'top-16')"
        :selected-count="selectedVariants.length"
        :grouping-options="unselectedBoops"
        :metric-options="metricOptions"
        :valid-combinations-count="validCombinationsCount"
      />
      <!-- <ProductFinderCompareProducersList
        v-if="producers.length > 0"
        :producers="producers"
        :un-selected-producers="unSelectedProducers"
        @toggle-producer="emit('toggle-producer', $event)"
      /> -->
      <!-- All variants -->
      <ProductFinderCompareVariantList
        v-if="!groupBy && !comparisonActive"
        :title="`${$t('All {title} variants', { title: category.name })} (${sortedProductsOnlyProducers.length})`"
        :variants="sortedProducts"
        :metric="metric"
        :selected-variants="selectedVariants"
        :selected-options="selectedOptions"
        :active-group="groupBy"
        :comparing-mode="comparisonActive"
        :sidebar-closed="sidebarClosed"
        @toggle-variant="toggleVariant"
        @order-variant="orderVariant"
      />
      <!-- Comparing variants -->
      <ProductFinderCompareVariantList
        v-if="comparisonActive"
        v-model:sort-comparing-by="sortSelectedBy"
        :title="`${$t('Comparing')} (${selectedVariants.length})`"
        :variants="sortedSelectedVariants"
        :selected-variants="selectedVariants"
        :metric="metric"
        :selected-options="selectedOptions"
        :active-group="groupBy"
        :sidebar-closed="sidebarClosed"
        is-comparing
        @toggle-variant="toggleVariant"
        @order-variant="orderVariant"
      />
      <!-- Grouped variants -->
      <div v-else-if="groupBy">
        <ProductFinderCompareVariantGroupHeader
          v-model:sort-group-by="sortGroupBy"
          v-model:chosen-group-name="chosenGroupName"
          :product-groups="sortedProductGroups"
        />
        <ProductFinderCompareVariantList
          v-if="chosenGroupName"
          :variants="sortedProductGroups[chosenGroupName]"
          :metric="metric"
          :selected-variants="selectedVariants"
          :selected-options="selectedOptions"
          :active-group="groupBy"
          :comparing-mode="comparisonActive"
          :sidebar-closed="sidebarClosed"
          @toggle-variant="toggleVariant"
          @order-variant="orderVariant"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  products: {
    type: Array,
    required: true,
  },
  unselectedBoops: {
    type: Array,
    default: () => [],
  },
  selectedOptions: {
    type: Object,
    default: () => {},
  },
  selectedBoops: {
    type: Array,
    default: () => [],
  },
  sidebarClosed: {
    type: Boolean,
    default: false,
  },
  producers: {
    type: Array,
    default: () => [],
  },
  unSelectedProducers: {
    type: Array,
    default: () => [],
  },
  sticking: {
    type: Boolean,
    default: false,
  },
});

const comparisonActive = defineModel("comparisonActive", { type: Boolean, default: false });

const emit = defineEmits(["order-variant", "toggle-producer"]);

const { t: $t } = useI18n();
const { cn } = useUtilities();
const { getDisplayName } = useDisplayName();

const store = useProductFinderStore();
const activeSales = computed(() => store.activeQuotation || store.activeOrder);

/**
 * Compare Management
 */
const sortSelectedBy = ref("delivery-asc");
const selectedVariants = ref([]);
function toggleVariant(variantId) {
  if (selectedVariants.value.some((product) => product.id === variantId)) {
    selectedVariants.value = selectedVariants.value.filter((product) => product.id !== variantId);
  } else {
    const variant = props.products.find((product) => product.id === variantId);
    selectedVariants.value.push(variant);
  }
}

const sortProducts = (products, sortBy) => {
  switch (sortBy) {
    case "delivery-asc":
      return [...products].sort((a, b) =>
        a.bestDelivery?.price.dlv.actual_days && b.bestDelivery?.price.dlv.actual_days
          ? a.bestDelivery?.price.dlv.actual_days - b.bestDelivery?.price.dlv.actual_days
          : 0,
      );
    case "delivery-desc":
      return [...products].sort((a, b) =>
        b.bestDelivery?.price.dlv.actual_days && a.bestDelivery?.price.dlv.actual_days
          ? b.bestDelivery?.price.dlv.actual_days - a.bestDelivery?.price.dlv.actual_days
          : 0,
      );
    case "price-asc":
      return [...products].sort((a, b) =>
        a.bestPrice?.price.p && b.bestPrice?.price.p
          ? a.bestPrice?.price.p - b.bestPrice?.price.p
          : 0,
      );
    case "price-desc":
      return [...products].sort((a, b) =>
        a.bestPrice?.price.p && b.bestPrice?.price.p
          ? b.bestPrice?.price.p - a.bestPrice?.price.p
          : 0,
      );
    case "producers-asc":
      return [...products].sort((a, b) => a.producers.length - b.producers.length);
    case "producers-desc":
      return [...products].sort((a, b) => b.producers.length - a.producers.length);
    case "boops-asc":
      return [...products].sort(
        (a, b) => Object.keys(a.items).length - Object.keys(b.items).length,
      );
    case "boops-desc":
      return [...products].sort(
        (a, b) => Object.keys(b.items).length - Object.keys(a.items).length,
      );
    default:
      return products;
  }
};
const sortedProducts = computed(() => sortProducts(props.products, sortBy.value));
const sortedProductsOnlyProducers = computed(() =>
  sortedProducts.value.filter((product) => product.producers.length > 0),
);

const sortedSelectedVariants = ref([]);
watch(
  [selectedVariants, () => props.products, sortSelectedBy],
  () => {
    const filteredSelectedVariants = [...selectedVariants.value].map((variant) => ({
      ...variant,
      producers: variant.producers.filter((p) => !props.unSelectedProducers.includes(p.id)),
    }));
    sortedSelectedVariants.value = sortProducts(filteredSelectedVariants, sortSelectedBy.value);
  },
  { immediate: true, deep: true },
);

/**
 * Comparison Management
 */
watch(comparisonActive, (value) => !value && (selectedVariants.value = []));

/**
 * Metric Management
 */
const metric = ref("deliveryTime");
const metricOptions = [
  {
    label: $t("Delivery time"),
    value: "deliveryTime",
    icon: "far truck",
  },
  {
    label: $t("Quantity"),
    value: "quantity",
    icon: "far boxes",
  },
  {
    label: $t("Price"),
    value: "price",
    icon: "far dollar-sign",
  },
];

/**
 * Sorting Management
 */
const sortBy = ref("delivery-asc");

/**
 * Grouping Management
 */
const groupBy = ref(null);
const productGroups = ref([]);
const chosenGroupName = ref(null);
const sortGroupBy = ref("delivery-asc");
watch(sortBy, (value) => (sortGroupBy.value = value));
const validCombinationsCount = computed(() => {
  if (!groupBy.value && !comparisonActive.value) {
    return sortedProductsOnlyProducers.value.length;
  } else if (groupBy.value && chosenGroupName.value) {
    return sortedProductGroups.value[chosenGroupName.value]?.length || 0;
  } else {
    return selectedVariants.value.length;
  }
});
function groupVariantsBy(key) {
  if (!key) return { "All variants": sortedProducts.value };

  const prods = sortedProducts.value.reduce((acc, variant) => {
    const group = Object.values(variant.items).find((value) => value.linked_key === key);
    const groupKey = getDisplayName(group?.value_display_name) ?? group?.key;

    if (!groupKey) return acc;

    if (!acc[groupKey]) {
      acc[groupKey] = [];
    }
    acc[groupKey].push(variant);
    return acc;
  }, {});
  return prods;
}

watch(
  [groupBy, () => props.products, sortBy],
  () => {
    if (groupBy?.value) {
      productGroups.value = groupVariantsBy(groupBy.value.linked);
      chosenGroupName.value = Object.keys(productGroups.value)[0];
    }
  },
  { immediate: true, deep: true },
);

const sortedProductGroups = computed(() => {
  return Object.fromEntries(
    Object.entries(productGroups.value).map(([key, value]) => {
      return [key, sortProducts(value, sortGroupBy.value)];
    }),
  );
});

/**
 * Ordering Management
 */
const orderVariant = (variantId) => emit("order-variant", variantId);
</script>

<style scoped>
.sticking {
  @apply bg-white dark:bg-gray-900;
}
</style>
