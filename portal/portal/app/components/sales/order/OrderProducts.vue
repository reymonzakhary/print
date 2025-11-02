<template>
  <div v-if="showZeroState">
    <SalesZeroState
      :disabled="criticalFlag"
      @on-finder-product="handleAddFinderProduct"
      @on-assortment-product="handleAddAssortmentProduct"
      @on-open-product="handleAddOpenProduct"
    />
  </div>
  <div
    v-else
    class="grid grid-cols-1 gap-4 overflow-visible xl:grid-cols-2 2xl:grid-cols-3 print:grid-cols-2"
    :class="{ '!grid-cols-1': isExternal }"
  >
    <OrderProduct
      v-for="item in props.items"
      :key="item.product.price.id ?? item.id"
      class="h-full"
      :item="item"
      :is-archived="isArchived"
      :order-id="props.salesId"
      @on-duplicate-item="handleDuplicateItem(item)"
      @on-remove-item="handleRemoveItem(item.id)"
      @on-item-updated="handleItemUpdated"
    />
    <FreeProduct
      v-if="showOpenProduct"
      @on-save-product="handleSaveOpenProduct"
      @on-close="showOpenProduct = false"
    />
    <SalesAddTile
      v-if="
        !showOpenProduct &&
        isEditable &&
        !isExternal &&
        permissions.includes('quotations-items-create')
      "
      class="print:hidden"
      @on-finder-product="handleAddFinderProduct"
      @on-assortment-product="handleAddAssortmentProduct"
      @on-open-product="handleAddOpenProduct"
    />
  </div>
</template>

<script setup>
import { useStore } from "vuex";

const props = defineProps({
  salesId: {
    type: [String, Number],
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  isArchived: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "on-product-added",
  "on-product-removed",
  "on-product-updated",
  "on-open-product-added",
]);

const store = useStore();
const { t: $t } = useI18n();

const { permissions } = storeToRefs(useAuthStore());
const { isEditable, isExternal, saving, criticalFlag } = storeToRefs(useSalesStore());
const orderRepository = useOrderRepository();

const { handleError } = useMessageHandler();
const { confirm } = useConfirmation();
const { addToast } = useToastStore();

const showOpenProduct = ref(false);

const showZeroState = computed(() => !props.items.length && !showOpenProduct.value);

async function handleItemUpdated(updatedItem) {
  emit("on-product-updated", updatedItem);
}

async function handleDuplicateItem(item) {
  try {
    saving.value = true;
    const newItemData = JSON.parse(JSON.stringify(item));
    if (newItemData?.product?.price?.selling_price_ex != null) {
      newItemData.product.price.selling_price_ex *= 100;
    }
    if (newItemData.product.price.selling_price_inc != null) {
      newItemData.product.price.selling_price_inc *= 100;
    }
    const newItem = await orderRepository.addItemToOrder(props.salesId, newItemData.product);

    // Update shipping cost because there's no way to update it in the addItemToQuotation method
    newItem.shipping_cost = item.shipping_cost;
    await orderRepository.updateItem({
      orderId: props.salesId,
      itemId: newItem.id,
      data: {
        shipping_cost: item.shipping_cost,
        product: {
          price: { qty: newItem.qty, vat: newItem?.product?.price?.vat },
        },
      },
    });

    emit("on-product-added", newItem);
    addToast({
      type: "success",
      message: $t("product succesfully duplicated"),
    });
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleRemoveItem(itemId) {
  saving.value = true;
  try {
    await confirm({
      title: $t("remove product"),
      message: $t("are you sure you want to remove this product?"),
      confirmOptions: {
        label: $t("remove"),
        variant: "danger",
      },
    });
    await orderRepository.removeItemFromOrder(props.salesId, itemId);
    emit("on-product-removed", itemId);
    addToast({
      type: "success",
      message: $t("product succesfully removed"),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

const productFinderStore = useProductFinderStore();
async function handleAddFinderProduct() {
  productFinderStore.activeOrder = props.salesId;
  return navigateTo("/marketplace/product-finder");
}

async function handleAddAssortmentProduct() {
  store.commit("orders/set_active_order", props.salesId);
  store.commit("compare/set_flag", "add_product");
  store.commit("orders/set_active_order_type", "order");
  return navigateTo({
    path: "/assortment",
  });
}

function handleAddOpenProduct() {
  showOpenProduct.value = true;
}

async function handleSaveOpenProduct(product) {
  try {
    const data = await orderRepository.addItemToOrder(props.salesId, product);
    showOpenProduct.value = false;
    emit("on-open-product-added", data);
  } catch (err) {
    handleError(err);
  }
}

onBeforeRouteLeave((_, __, next) => {
  if (showOpenProduct.value) {
    addToast({
      type: "error",
      message: $t("please save or cancel the open product first"),
    });
    return next(false);
  }

  next();
});
</script>
