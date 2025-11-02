<template>
  <section class="flex h-full flex-col">
    <article
      v-if="variant"
      class="flex flex-col space-y-6 overflow-hidden overflow-y-auto bg-gray-50 px-6 pt-6 dark:bg-gray-800"
    >
      <!-- Preview header -->
      <header
        class="relative overflow-hidden rounded-xl border bg-white p-4 shadow dark:border-gray-900 dark:bg-gray-700"
      >
        <ProductFinderOrderModalPreviewInfo
          ref="productImagePreviewRef"
          :image="selectedCategory?.image || variant.image || null"
          :title="selectedCategory?.name || variant.name || 'Product Details'"
          :loading="false"
          :quantity="variant.quantity"
          :selected-delivery-time="selectedProducerOption?.deliveryTime"
          :product-properties="variant.items || {}"
        >
          <!-- <ProductFinderOrderModalPreviewSelectedProducer :producer="selectedProducerOption" /> -->
          <ProductFinderOrderModalPreviewQuickButtons
            :is-selected-cheapest="isSelectedCheapest"
            :is-selected-quickest="isSelectedQuickest"
            :cheapest-producer-logo="cheapestOption?.producerLogo"
            :quickest-producer-logo="quickestOption?.producerLogo"
            @select-cheapest="$emit('select-cheapest')"
            @select-quickest="$emit('select-quickest')"
          />
        </ProductFinderOrderModalPreviewInfo>
      </header>

      <!-- Producer Selection Accordions -->
      <div class="mt-6 flex flex-1 flex-col space-y-2 overflow-y-auto">
        <h3 class="font-medium text-gray-900 dark:text-white">
          {{ $t("Select a producer") }}
        </h3>
        <ProductFinderOrderModalPreviewProducersList
          :producers="variant.producers"
          :expanded-producer-ids="expandedProducerIds"
          :selected-producer-option="selectedProducerOption"
          :price-options="priceOptions"
          :cheapest-option="cheapestOption"
          :quickest-option="quickestOption"
          @toggle-producer="$emit('toggle-producer', $event)"
          @select-option="$emit('select-producer-option', $event)"
        />
      </div>

      <!-- Additional Options Selection (checkboxes) -->
      <ProductFinderOrderModalPreviewSelection :options="variant.options" />
    </article>

    <!-- Empty state when no item is selected -->
    <article v-else class="flex h-full flex-col items-center justify-center p-6 text-center">
      <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-700">
        <font-awesome-icon
          :icon="['fas', 'shopping-cart']"
          class="text-3xl text-gray-400 dark:text-gray-500"
        />
      </div>
      <h3 class="mb-2 text-xl font-medium text-gray-900 dark:text-white">
        {{ $t("No product selected") }}
      </h3>
      <p class="max-w-md text-gray-600 dark:text-gray-400">
        {{ $t('Click on "Order" button on any product to preview and add it to your basket.') }}
      </p>
    </article>

    <div class="mt-auto flex gap-3 border-t bg-white p-3 dark:border-gray-900 dark:bg-gray-700">
      <button
        class="flex-1 rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 outline outline-1 outline-gray-200 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:outline-gray-900 dark:hover:bg-gray-600"
        @click="$emit('close')"
      >
        {{ $t("Continue shopping") }}
      </button>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: Object,
    default: null,
  },
  selectedCategory: {
    type: Object,
    default: null,
  },
  expandedProducerIds: {
    type: Set,
    default: () => new Set(),
  },
  selectedProducerOption: {
    type: Object,
    default: null,
  },
  priceOptions: {
    type: Array,
    default: () => [],
  },
  cheapestOption: {
    type: Object,
    default: null,
  },
  quickestOption: {
    type: Object,
    default: null,
  },
});

defineEmits([
  "close",
  "toggle-producer",
  "select-producer-option",
  "select-cheapest",
  "select-quickest",
]);

const productImagePreviewRef = ref(null);
const { t: $t } = useI18n();

const categoryTitle = computed(() => {
  if (props.selectedCategory) return props.selectedCategory.name;
  if (props.variant) return props.variant.name;
  return $t("Product Details");
});

/**
 * Selection States
 */
const isSelectedCheapest = computed(() => {
  if (!props.selectedProducerOption || !props.cheapestOption) return false;
  return props.selectedProducerOption.id === props.cheapestOption.id;
});

const isSelectedQuickest = computed(() => {
  if (!props.selectedProducerOption || !props.quickestOption) return false;
  return props.selectedProducerOption.id === props.quickestOption.id;
});

// Expose the preview ref for animation purposes
defineExpose({
  productImagePreviewRef,
});
</script>
