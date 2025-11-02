<template>
  <div class="space-y-3 2xl:space-y-4">
    <div class="flex items-center justify-between">
      <ProductFinderUITitle v-if="title" :title="title" />
      <ProductFinderCompareToolbarSortMenu v-if="isComparing" v-model="sortComparingBy" />
    </div>
    <UIMasonry
      v-if="variantsWithProducers.length"
      :key="amountOfColumns + margin"
      :columns="amountOfColumns"
      :margin="margin"
    >
      <template v-for="variant in variantsWithProducers" :key="variant.id">
        <ProductFinderCompareVariantCard
          :variant="variant"
          :selected-options="selectedOptions"
          :metric="metric"
          :is-selected="selectedVariants.some((product) => product.id === variant.id)"
          :active-group="activeGroup"
          :common-boops="findCommonBoops(variantsWithProducers)"
          :unique-boxes="findUniqueBoxes(variantsWithProducers)"
          :is-comparing="isComparing"
          @toggle-selection="$emit('toggle-variant', variant.id)"
          @order-variant="$emit('order-variant', variant.id)"
        />
      </template>
    </UIMasonry>
    <ProductFinderCompareVariantZeroState v-else />
    <template v-if="!isComparing">
      <button
        :class="
          cn(
            'flex w-full items-center justify-between',
            !variantsWithoutProducers.length && 'cursor-default opacity-50',
            variantsWithoutProducers.length && 'group',
            noProducersCollapsed && 'pb-12',
          )
        "
        :disabled="!variantsWithoutProducers.length"
        @click="noProducersCollapsed = !noProducersCollapsed"
      >
        <ProductFinderUITitle
          :title="
            (() => {
              // eslint-disable-next-line
              return $t('View {count} variants without producers', {
                count: variantsWithoutProducers.length,
              });
            })()
          "
          class="mb-0 group-hover:text-theme-700"
        />
        <font-awesome-icon
          :icon="['fas', 'chevron-down']"
          class="h-4 w-4 transform text-gray-400 transition-transform group-hover:text-theme-700"
          :class="{ 'rotate-180': noProducersCollapsed }"
        />
      </button>
      <UIMasonry
        v-if="variantsWithoutProducers.length"
        :key="amountOfColumns + margin"
        :columns="amountOfColumns"
        :class="[
          'overflow-hidden transition-all duration-300 ease-in-out',
          { 'max-h-0': noProducersCollapsed, 'max-h-[10000px]': !noProducersCollapsed },
        ]"
      >
        <template
          v-for="variant in variants.filter((variant) => variant.producers.length === 0)"
          :key="variant.id"
        >
          <ProductFinderCompareVariantCard
            :variant="variant"
            :selected-options="selectedOptions"
            :metric="metric"
            :is-selected="selectedVariants.some((product) => product.id === variant.id)"
            :active-group="activeGroup"
            :disabled="true"
          />
        </template>
      </UIMasonry>
    </template>
  </div>
</template>

<script setup>
const props = defineProps({
  variants: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    default: null,
  },
  metric: {
    type: String,
    default: "deliveryTime",
  },
  selectedVariants: {
    type: Array,
    default: () => [],
  },
  selectedOptions: {
    type: Object,
    default: () => {},
  },
  selectedGroupName: {
    type: String,
    default: null,
  },
  activeGroup: {
    type: Object,
    default: null,
  },
  isComparing: {
    type: Boolean,
    default: false,
  },
  sidebarClosed: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["toggle-variant", "order-variant"]);

const sortComparingBy = defineModel("sortComparingBy", { type: String, default: "delivery-asc" });

const { cn } = useUtilities();

const variants = computed(() => props.variants);
const variantsWithProducers = computed(
  () => variants.value?.filter((variant) => variant.producers.length > 0) || [],
);
const variantsWithoutProducers = computed(
  () => variants.value?.filter((variant) => variant.producers.length === 0) || [],
);

const { $screen } = useNuxtApp();
const amountOfColumns = computed(() => {
  switch ($screen.breakpoint) {
    case "2xl":
    case "xl":
      if (props.sidebarClosed) return 4;
      return 3;
    case "lg":
    case "md":
      if (props.sidebarClosed) return 3;
      return 2;
    case "sm":
      if (props.sidebarClosed) return 2;
      return 1;
    default:
      return 1;
  }
});
const margin = computed(() => {
  switch ($screen.breakpoint) {
    case "2xl":
      return 20;
    default:
      return 10;
  }
});

/**
 * Collapse no producers variants
 */
const noProducersCollapsed = ref(true);
watch(
  () => props.variants,
  () => {
    if (variantsWithProducers.value.length === 0) {
      noProducersCollapsed.value = false;
    }
  },
  { immediate: true },
);

/**
 * Finds common boops across all variants
 * Returns an array of linked_key values
 */
const findCommonBoops = (variants) => {
  if (!variants || variants.length === 0) return [];

  // Get all boops from the first variant to check against others
  const firstVariantBoops = variants[0].product || [];

  // Track which linked_keys have the same linked_value across all variants
  const commonLinkedKeys = [];

  // Check each boop from the first variant
  firstVariantBoops.forEach((boop) => {
    const linkedKey = boop.linked_key;
    const linkedValue = boop.linked_value;

    // Check if this linked_key and linked_value exists in all other variants
    const isCommonAcrossAllVariants = variants.every((variant) => {
      const variantBoops = variant.items || [];
      return variantBoops.some(
        (vBoop) => vBoop.linked_key === linkedKey && vBoop.linked_value === linkedValue,
      );
    });

    // If this linked_key is common across all variants, add it to our result
    if (isCommonAcrossAllVariants && !commonLinkedKeys.includes(linkedKey)) {
      commonLinkedKeys.push(linkedKey);
    }
  });

  return commonLinkedKeys;
};

/**
 * Finds boxes that are not in all variants
 * Returns an array of linked_key values
 */
const findUniqueBoxes = (variants) => {
  if (!variants || variants.length === 0) return [];

  // Get all unique linked_keys across all variants
  const allLinkedKeys = new Set(
    variants.flatMap((variant) => (variant.items || []).map((boop) => boop.linked_key)),
  );

  // Find linked_keys that are not present in all variants
  const uniqueLinkedKeys = Array.from(allLinkedKeys).filter((linkedKey) => {
    return !variants.every((variant) =>
      (variant.items || []).some((boop) => boop.linked_key === linkedKey),
    );
  });

  return uniqueLinkedKeys;
};
</script>
