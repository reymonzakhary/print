<template>
  <div
    :class="
      cn(
        'flex max-h-[36rem] w-96 flex-col space-y-4 rounded-md bg-white p-4 shadow-xl dark:bg-gray-800',
        props.class,
      )
    "
  >
    <header class="space-y-4 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">
        {{ $t("Your basket") }}
      </h3>

      <div class="flex items-end justify-between">
        <div>
          <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t("Total amount") }}</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ basketTotal }}
          </div>
        </div>

        <div v-if="basketItems.length > 0">
          <button
            class="rounded border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            :disabled="isSubmitting"
            @click="handleClearBasket"
          >
            <font-awesome-icon :icon="['fas', 'trash']" class="mr-1" />
            {{ $t("Clear Basket") }}
          </button>
        </div>
      </div>
    </header>

    <Separator />

    <!-- Basket items list -->
    <div class="flex-1 overflow-y-auto">
      <div v-if="basketItems.length === 0" class="py-8 text-center">
        <font-awesome-icon
          :icon="['fad', 'bag-shopping']"
          class="mb-2 text-4xl text-gray-200 dark:text-gray-700"
        />
        <p class="text-gray-500 dark:text-gray-400">{{ $t("Your basket is empty") }}</p>
      </div>
      <ul v-else-if="true" class="space-y-3 overflow-y-auto">
        <ProductFinderBasketItem
          v-for="(item, index) in basketItems"
          :key="item.productId || index"
          :item="item"
          :is-expanded="expandedBasketItemIds.has(item.productId)"
          :disabled="isSubmitting"
          compact
          @toggle="handleToggleBasketItem(item.productId)"
          @remove="handleRemoveBasketItem(index)"
        />
      </ul>
      <ul v-else class="space-y-3">
        <li
          v-for="(item, index) in basketItems"
          :key="item.productId || index"
          class="group cursor-pointer space-y-2 rounded-lg border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        >
          <!-- Always visible item summary -->
          <div class="p-3" @click="handleToggleBasketItem(item.productId)">
            <div class="flex items-start gap-3">
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.productName"
                class="h-12 w-12 rounded-md object-cover"
              />
              <div class="flex-1">
                <div class="flex items-start justify-between">
                  <h4 class="font-medium text-gray-900 dark:text-white">
                    {{ item.productName || "Unknown Product" }}
                  </h4>
                  <div class="flex items-center gap-2">
                    <button
                      class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                      @click.stop="handleRemoveBasketItem(index)"
                    >
                      <font-awesome-icon :icon="['fas', 'trash']" />
                    </button>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                      {{ item.price || "N/A" }}
                    </div>
                  </div>
                </div>

                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  <span>{{ item.producer }}</span>
                  <span v-if="item.deliveryTime" class="ml-2">· {{ item.deliveryTime }}</span>
                  <span v-if="item.quantity" class="ml-2"
                    >· {{ item.quantity }} {{ $t("items") }}</span
                  >
                </div>

                <!-- Accordion toggle -->
                <div class="mt-2 flex w-full items-center justify-between text-xs text-theme-500">
                  <span>{{
                    expandedBasketItemIds.has(item.productId)
                      ? $t("Hide details")
                      : $t("Show details")
                  }}</span>
                  <font-awesome-icon
                    :icon="['fas', 'chevron-down']"
                    :class="{ 'rotate-180': expandedBasketItemIds.has(item.productId) }"
                    class="transition-transform duration-300"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Details content -->
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-96 opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="max-h-96 opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="expandedBasketItemIds.has(item.productId)" class="overflow-hidden">
              <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                <ProductFinderOrderModalBoopsList
                  v-if="item.completeData?.variant?.items"
                  :product="item.completeData.variant.items"
                />
                <div v-else class="grid grid-cols-2 gap-x-6 gap-y-3">
                  <ProductFinderOrderModalBoopsListItem
                    v-if="item.deliveryTime"
                    :label="$t('Delivery')"
                    :value="item.deliveryTime"
                  />
                  <ProductFinderOrderModalBoopsListItem
                    v-if="item.quantity"
                    :label="$t('Quantity')"
                    :value="String(item.quantity)"
                  />
                  <ProductFinderOrderModalBoopsListItem
                    v-if="item.producer"
                    :label="$t('Producer')"
                    :value="item.producer"
                  />
                </div>
              </div>
            </div>
          </Transition>
        </li>
      </ul>
    </div>

    <!-- Footer with action buttons -->
    <div class="flex gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
      <ProductFinderOrderModalButton
        type="quotation"
        :icon="['fas', 'file-invoice']"
        :disabled="isSubmitting || basketItems.length === 0"
        :loading="isSubmitting"
        :active-id="productFinderStore.activeQuotation"
        @click="handleCreateOrderWithType('quotations')"
      />
      <ProductFinderOrderModalButton
        type="order"
        :icon="['fas', 'shopping-cart']"
        :disabled="isSubmitting || basketItems.length === 0"
        :loading="isSubmitting"
        :active-id="productFinderStore.activeOrder"
        @click="handleCreateOrderWithType('orders')"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  class: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["order-created"]);

const { t: $t } = useI18n();
const { cn } = useUtilities();
const productFinderStore = useProductFinderStore();

const orderRepository = useOrderRepository();
const quotationRepository = useQuotationRepository();
const messageHandler = useMessageHandler();
const toastStore = useToastStore();

const { createOrderWithType } = useOrderCreation(
  orderRepository,
  quotationRepository,
  messageHandler,
  toastStore,
);

// Get basket data from store
const isSubmitting = ref(false);
const expandedBasketItemIds = ref(new Set());

const basketItems = computed(() => productFinderStore.basketItems);
const basketTotal = computed(() => productFinderStore.basketTotal);

const handleToggleBasketItem = (itemId) => {
  const newSet = new Set(expandedBasketItemIds.value);
  if (newSet.has(itemId)) {
    newSet.delete(itemId);
  } else {
    newSet.add(itemId);
  }
  expandedBasketItemIds.value = newSet;
};

const handleRemoveBasketItem = (index) => {
  productFinderStore.removeItemFromBasket(index);
  toastStore.addToast({ type: "info", message: $t("Item removed from basket") });
};

const handleClearBasket = () => {
  productFinderStore.clearBasket();
  toastStore.addToast({ type: "info", message: $t("Basket cleared") });
};

const handleCreateOrderWithType = async (type) => {
  try {
    const targetId = await createOrderWithType(
      type,
      productFinderStore.basketItems,
      productFinderStore.activeOrder,
      productFinderStore.activeQuotation,
    );

    if (targetId) {
      productFinderStore.clearBasket();
      emit("order-created", targetId);

      if (type === "orders") {
        if (productFinderStore.activeOrder) productFinderStore.activeOrder = null;
        navigateTo(`/orders/${targetId}`);
      } else {
        if (productFinderStore.activeQuotation) productFinderStore.activeQuotation = null;
        navigateTo(`/quotations/${targetId}`);
      }
    }
  } catch (error) {
    console.error(`Error handling order creation:`, error);
  }
};
</script>
