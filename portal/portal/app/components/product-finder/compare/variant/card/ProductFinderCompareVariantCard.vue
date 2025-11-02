<template>
  <div class="relative">
    <article
      ref="articleRef"
      :class="
        cn(
          'product-finder-compare-variant-card static w-full rounded-md border bg-white shadow-md shadow-gray-300/50 outline outline-1 outline-transparent transition-all duration-200 dark:border-gray-900 dark:bg-gray-700 dark:text-white dark:shadow-black/50',
          isSelected &&
            !isComparing &&
            '!dark:outline-theme-600 outline-theme-500 hover:border-gray-200 dark:outline-theme-600',
          !isComparing && !disabled && 'hover:outline-theme-500 dark:hover:outline-theme-600',
          disabled && 'cursor-default opacity-50',
          !showDetails ? 'no-details' : '',
          showDetails && 'border-theme-200 dark:border-theme-200',
          $attrs.class,
        )
      "
    >
      <section class="flex cursor-pointer" @click="showDetails = !showDetails">
        <ProductFinderCompareVariantCardOptions
          v-if="shownBoops.length > 0"
          :boops="shownBoops"
          class="w-2/3 border-r border-gray-200 dark:border-gray-900"
        />
        <ProductFinderCompareVariantCardHeader
          class="w-1/3"
          :delivery-time="`${variant.bestDelivery.price.dlv.actual_days}`"
          :price="variant.bestPrice.price.display_selling_price_ex"
          :quantity="variant.quantity"
          :metric="metric"
        />
      </section>
      <ProductFinderCompareVariantCardFooter
        v-model:show-details="showDetails"
        :producers="variant.producers"
        :is-selected="isSelected"
        :is-comparing="isComparing"
        :show-details="showDetails"
        class="border-t border-gray-200 dark:border-gray-900"
        @toggle-selection="emit('toggle-selection')"
        @order-variant="emit('order-variant')"
      />
    </article>
    <!-- <UICollapsible> -->
    <Transition name="slide">
      <ProductFinderCompareVariantCardDetails
        v-if="showDetails"
        :variant="variant"
        :best-price="variant.bestPrice"
        :best-delivery-time="variant.bestDelivery"
        :producers="variant.producers"
        :show-details="showDetails"
        class="pointer-events-none relative -z-10 -mt-2 w-full"
        @select-delivery="handleSelectDelivery"
        @select-price="handleSelectPrice"
        @close-details="showDetails = false"
      />
    </Transition>

    <!-- Invisible overlay buttons positioned over the detail buttons -->
    <div
      v-if="showDetails"
      ref="overlayRef"
      class="pointer-events-none absolute inset-x-0 z-20 h-32"
      :style="{ top: articleHeight + 'px' }"
    >
      <div class="mx-1 mt-4 grid h-full grid-cols-2 gap-2 p-4">
        <!-- Invisible overlay for delivery button -->
        <button
          class="pointer-events-auto h-full rounded-md bg-theme-500 opacity-0 transition-opacity hover:opacity-10"
          @click="handleSelectDelivery(variant.bestDelivery)"
        >
          <!-- Delivery -->
        </button>
        <!-- Invisible overlay for price button -->
        <button
          class="pointer-events-auto rounded-md bg-green-500 opacity-0 transition-opacity hover:opacity-10"
          @click="handleSelectPrice(variant.bestPrice)"
        >
          <!-- Price -->
        </button>
      </div>
    </div>

    <!-- </UICollapsible> -->
  </div>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: Object,
    required: true,
  },
  selectedOptions: {
    type: Object,
    default: () => {},
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
  metric: {
    type: String,
    default: "deliveryTime",
  },
  activeGroup: {
    type: [Object, null],
    default: null,
  },
  commonBoops: {
    type: [Array, Boolean],
    default: () => [],
  },
  uniqueBoxes: {
    type: [Array, Boolean],
    default: () => [],
  },
  isComparing: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-selection", "order-variant"]);

const { cn } = useUtilities();

const showDetails = ref(false);
const overlayRef = ref(null);
const articleRef = ref(null);

const productFinderStore = useProductFinderStore();
const toastStore = useToastStore();
const { t: $t } = useI18n();

// Calculate the height of the main article to position the overlay correctly
const articleHeight = computed(() => {
  if (articleRef.value) {
    return articleRef.value.offsetHeight - 24;
  }
  return 120; // Fallback height
});

/**
 * If we decide to show the selected boops in the card, we can just return the following:
 */
const boops = computed(() => props.variant.items ?? []);
const shownBoops = computed(() => {
  return boops.value
    ? boops.value.map((boop) => {
        return {
          ...boop,
          isUnique: props.uniqueBoxes.length > 0 && props.uniqueBoxes.includes(boop.linked_key),
          isCommon: props.commonBoops.length > 0 && props.commonBoops.includes(boop.linked_key),
          isSelected:
            !!props.selectedOptions[boop.linked_key] ||
            (!props.isComparing && props.activeGroup?.id === boop.linked_key),
          isManual: !!props.selectedOptions[boop.linked_key],
        };
      })
    : [];
});

// Handlers for quickest delivery and best price selection with proper order/quotation data
const handleSelectDelivery = (deliveryOption) => {
  console.log("🚚 Selected quickest delivery:", deliveryOption);

  // Create basket item for quickest delivery option with proper structure for order creation
  const basketItem = {
    productId: `${props.variant.id}_delivery_${Date.now()}`,
    productName: deliveryOption.tenant.category.name ?? "Product (Quickest Delivery)",
    quantity: props.variant.quantity,
    price: deliveryOption.price?.display_selling_price_ex,
    priceNumeric: deliveryOption.price?.p || deliveryOption.price?.price_numeric,
    deliveryTime: deliveryOption.price?.dlv?.actual_days,
    producer: deliveryOption.tenant?.name,
    producerId: deliveryOption.tenant?.id,
    type: "quickest_delivery",
    completeData: {
      variant: props.variant,
      option: {
        price: deliveryOption.price,
        originalPriceData: deliveryOption.price,
        originalProducerData: deliveryOption.tenant,
        priceNumeric: deliveryOption.price?.p || deliveryOption.price?.price_numeric,
        deliveryTime: deliveryOption.price?.dlv?.actual_days,
        producerId: deliveryOption.tenant?.id,
        producerName: deliveryOption.tenant?.name,
      },
      producerId: deliveryOption.tenant?.id,
    },
  };

  // Add to store and show success message
  productFinderStore.addItemToBasket(basketItem);
  toastStore.addToast({
    type: "success",
    message: $t("Quickest delivery option added to basket"),
  });
};

const handleSelectPrice = (priceOption) => {
  console.log("💰 Selected best price:", priceOption);

  // Create basket item for best price option with proper structure for order creation
  const basketItem = {
    productId: `${props.variant.id}_price_${Date.now()}`,
    productName: priceOption.tenant.category.name ?? "Product (Best Price)",
    quantity: props.variant.quantity,
    price: priceOption.price?.display_selling_price_ex,
    priceNumeric: priceOption.price?.p || priceOption.price?.price_numeric,
    deliveryTime: priceOption.price?.dlv?.actual_days,
    producer: priceOption.tenant?.name,
    producerId: priceOption.tenant?.id,
    type: "best_price",
    completeData: {
      variant: props.variant,
      option: {
        price: priceOption.price,
        originalPriceData: priceOption.price,
        originalProducerData: priceOption.tenant,
        priceNumeric: priceOption.price?.p || priceOption.price?.price_numeric,
        deliveryTime: priceOption.price?.dlv?.actual_days,
        producerId: priceOption.tenant?.id,
        producerName: priceOption.tenant?.name,
      },
      producerId: priceOption.tenant?.id,
    },
  };

  // Add to store and show success message
  productFinderStore.addItemToBasket(basketItem);
  toastStore.addToast({
    type: "success",
    message: $t("Best price option added to basket"),
  });
};
</script>

<style scoped>
.product-finder-compare-variant-card {
  /* Simulate a stacked card effect without blur for sharp shadows */
  /* box-shadow:
    0 6px 0px -2px #eee,
    0 10px 0px -3px #ddd,
    0 14px 0px -5px #ddd; */
}

.product-finder-compare-variant-card.no-details:before {
  content: "";
  position: absolute;
  background: white;
  top: 8px;
  left: 0;
  width: 98%;
  left: 1%;
  height: 100%;
  border-radius: inherit;
  @apply -z-20 border border-gray-200 bg-white shadow-md shadow-gray-300/50 dark:border-gray-900 dark:bg-gray-800 dark:shadow-black/70;
  /* z-index: -1; */
}
.product-finder-compare-variant-card.no-details:after {
  content: "";
  position: absolute;
  top: 14px;
  left: 0;
  width: 96%;
  left: 2%;
  height: 100%;
  border-radius: inherit;
  @apply -z-30 border border-gray-200 bg-white shadow-md shadow-gray-300/50 dark:border-gray-900 dark:bg-gray-900 dark:shadow-black/50;
}
</style>
