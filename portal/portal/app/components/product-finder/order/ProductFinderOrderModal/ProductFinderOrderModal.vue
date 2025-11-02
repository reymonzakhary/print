<template>
  <Teleport to="body">
    <ConfirmationModal
      v-if="isBasketOpen"
      classes="h-[80vh] -mt-[10vh] w-[40vw] bg-gray-100 dark:bg-gray-800"
      body-classes="!p-0 flex overflow-y-auto"
      no-footer
      @on-close="handleClose"
    >
      <template #modal-header>
        <h3 class="font-medium dark:text-white">
          <template v-if="selectedVariant">
            {{ $t("Product Selection") }}
          </template>
          <template v-else>
            {{ $t("Your basket") }}
          </template>
        </h3>
      </template>

      <!-- Forces the the modal 10% to the right for better visual balance -->
      <template #modal-wrapper-start>
        <div class="ml-[10%] h-[70vh] 2xl:ml-[16%]" />
      </template>

      <!-- Basket Panel -->
      <template #modal-wrapper-end>
        <div class="ml-4 h-[70vh]">
          <ProductFinderBasket
            ref="basketPanelRef"
            class="max-h-[70vh] cursor-default bg-gray-100 shadow-none"
          />
        </div>
      </template>

      <template #modal-body>
        <!-- Product Preview Panel -->
        <ProductFinderOrderModalPreview
          ref="productPreviewRef"
          class="w-full"
          :variant="selectedVariant"
          :selected-category="selectedCategory"
          :expanded-producer-ids="expandedProducerIds"
          :selected-producer-option="selectedProducerOption"
          :price-options="allPriceOptionsFlat"
          :cheapest-option="overallCheapestOption"
          :quickest-option="overallQuickestOption"
          @close="handleClose"
          @toggle-producer="handleToggleProducer"
          @select-producer-option="handleSelectProducerOption"
          @select-cheapest="handleSelectCheapestProducer"
          @select-quickest="handleSelectQuickestProducer"
        />
      </template>

      <!-- Footer for "Add to basket" action -->
      <template #modal-footer>
        <div
          class="flex w-full items-center justify-end gap-4 border-t bg-white p-4 dark:border-gray-700 dark:bg-gray-700"
        >
          <button
            class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900"
            @click="handleClose"
          >
            {{ $t("Cancel") }}
          </button>
          <button
            class="rounded-md bg-theme-500 px-4 py-2 text-sm font-medium text-white hover:bg-theme-600"
            :disabled="!selectedProducerOption"
            @click="handleAddToBasket"
          >
            {{ $t("Add to basket") }}
          </button>
        </div>
      </template>
    </ConfirmationModal>
  </Teleport>
</template>

<script setup>
const emit = defineEmits(["close", "add-to-basket"]);

const productFinderStore = useProductFinderStore();
const toastStore = useToastStore();
const { t: $t } = useI18n();

// Add a set to track items being processed
const processingQueue = ref(new Set());

// Component refs for animation
const productPreviewRef = ref(null);
const basketPanelRef = ref(null);

// Convenience computed props from store
const isBasketOpen = computed(() => productFinderStore.isBasketOpen);
const selectedVariant = computed(() => productFinderStore.selectedVariant);
const selectedCategory = computed(() => productFinderStore.selectedCategory);

// Composables for logic organization
const {
  expandedProducerIds,
  selectedProducerOption,
  createPriceOptionsList,
  findCheapestOption,
  findQuickestOption,
  toggleProducerAccordion,
} = useProducerSelection();

const { animateAddToBasket } = useBasketAnimation();

// Computed price options from variant data
const allPriceOptionsFlat = computed(() =>
  selectedVariant.value ? createPriceOptionsList(selectedVariant.value) : [],
);

// Find cheapest and quickest options
const overallCheapestOption = computed(() => findCheapestOption(allPriceOptionsFlat.value));

const overallQuickestOption = computed(() => findQuickestOption(allPriceOptionsFlat.value));

// Watch for changes in selectedVariant
watch(
  selectedVariant,
  () => {
    // Reset producer selection
    selectedProducerOption.value = null;
    // Reset expanded producer accordion
    expandedProducerIds.value.clear();
  },
  { immediate: true, deep: true },
);

// Event handlers
const handleClose = () => {
  if (selectedVariant.value && productFinderStore.clearSelectedVariant) {
    productFinderStore.clearSelectedVariant();
  }
  emit("close");
  productFinderStore.closeBasketDialog();
};

const handleToggleProducer = (producerId) => toggleProducerAccordion(producerId);

const handleSelectProducerOption = async (option) => {
  selectedProducerOption.value = option;
  expandedProducerIds.value.clear();
  await handleAddToBasket();
};

const handleSelectCheapestProducer = async () => {
  if (overallCheapestOption.value) {
    selectedProducerOption.value = overallCheapestOption.value;
    await handleAddToBasket();
  }
};

const handleSelectQuickestProducer = async () => {
  if (overallQuickestOption.value) {
    selectedProducerOption.value = overallQuickestOption.value;
    await handleAddToBasket();
  }
};

const generateProductId = (variant, option) => {
  return `${variant.id}_${option.priceNumeric}_${option.deliveryTime}_${option.producerId}`;
};

const handleAddToBasket = async () => {
  if (!selectedProducerOption.value || !selectedVariant.value) {
    toastStore.addToast({
      type: "error",
      message: $t("Please select a producer and delivery option."),
    });
    return;
  }

  // Create the basket item payload
  const variantData = selectedVariant.value;
  const chosenOption = selectedProducerOption.value;

  // Generate the unique product id
  const productId = generateProductId(selectedVariant.value, selectedProducerOption.value);
  // const isItemInBasket = productFinderStore.isItemInBasket(productId);

  // Check if the item is already in the basket or currently being processed
  // if (isItemInBasket || processingQueue.value.has(productId)) {
  //   toastStore.addToast({
  //     type: "error",
  //     // eslint-disable-next-line prettier/prettier
  //     message: $t("This item is already in your basket. Please select a higher quantity if desired."),
  //   });
  //   return;
  // } -----> It is okay to add the same product more than once, just with a different quantity

  // Add to processing queue
  processingQueue.value.add(productId);

  // Get source and target elements for animation
  const sourceElement = productPreviewRef.value?.$el;
  const targetElement = basketPanelRef.value?.$el;

  try {
    // Animation data
    const animationData = {
      name: selectedCategory.value?.name || selectedVariant.value.name,
      image: selectedCategory.value?.image || selectedVariant.value.image,
      price: selectedProducerOption.value.price,
    };

    // Animate the addition to basket
    await animateAddToBasket(sourceElement, targetElement, animationData);

    const basketItemPayload = {
      productId,
      productName: selectedCategory.value?.name || variantData.name || "Unknown Product",
      image: selectedCategory.value?.image || variantData.image || null,
      quantity: variantData.quantity,
      price: chosenOption.price,
      priceNumeric: chosenOption.priceNumeric,
      deliveryTime: chosenOption.deliveryTime,
      producer: chosenOption.producerName,
      producerId: chosenOption.producerId,
      completeData: {
        variant: { ...variantData },
        option: { ...chosenOption },
      },
    };

    // Add to basket
    productFinderStore.addItemToBasket(basketItemPayload);
    emit("add-to-basket", basketItemPayload);

    toastStore.addToast({ type: "success", message: $t("Item added to basket") });
  } catch (error) {
    console.error("Error adding item to basket:", error);

    // Fallback in case of animation failure - still add the item
    if (selectedVariant.value && selectedProducerOption.value) {
      const basketItemPayload = {
        productId: selectedVariant.value.id,
        productName:
          selectedCategory.value?.name || selectedVariant.value.name || "Unknown Product",
        image: selectedCategory.value?.image || selectedVariant.value.image || null,
        quantity: selectedVariant.value.quantity,
        price: selectedProducerOption.value.price,
        priceNumeric: selectedProducerOption.value.priceNumeric,
        deliveryTime: selectedProducerOption.value.deliveryTime,
        producer: selectedProducerOption.value.producerName,
        producerId: selectedProducerOption.value.producerId,
        completeData: {
          variant: { ...selectedVariant.value },
          option: { ...selectedProducerOption.value },
        },
      };

      productFinderStore.addItemToBasket(basketItemPayload);
      emit("add-to-basket", basketItemPayload);
      toastStore.addToast({ type: "success", message: $t("Item added to basket") });
    }
  } finally {
    // Remove from processing queue regardless of success or failure
    processingQueue.value.delete(productId);
  }
};
</script>
