<template>
  <NotFound v-if="!isInitializing && !quotation" />
  <main v-else-if="!error" class="h-full print:overflow-visible">
    <div class="grid h-full grid-rows-[64px_,_1fr] overflow-hidden print:!overflow-visible">
      <InvoiceModalPreview
        v-if="!isInitializing && quotation"
        :show="showInvoicePreview"
        :order-id="quotation.id"
        :order-number="quotation.order_nr"
        @on-close="showInvoicePreview = false"
        @on-backdrop-click="showInvoicePreview = false"
      />
      <SkeletonLine v-if="isInitializing" :height="8" class="m-4" />
      <OrderPageHeader
        v-else
        :order-number="quotation.order_nr"
        :external-id="quotation.external_id"
        class="p-4"
        :prev="quotationMeta.prev"
        :next="quotationMeta.next"
        :saving="saving"
        @on-close="handleCloseOrder"
      />
      <div class="hidden print:block" />
      <div
        class="grid h-full grid-cols-12 gap-4 overflow-y-auto rounded p-4 pt-0 print:!overflow-y-visible"
        :class="{
          'relative mx-[8px] h-[calc(100%_-_9px)]': criticalFlag,
          'outline-gray-300': criticalFlag === 'locked',
          'outline-red-300': criticalFlag === 'archived',
        }"
        :style="outlineStyle"
      >
        <div
          v-if="quotation && criticalFlag"
          class="fixed left-[calc(50%_+_64px)] top-[116px] z-50 w-fit -translate-x-1/2 rounded-b px-8 pb-1 text-sm font-bold shadow-md"
          :class="{
            'bg-gray-300 text-gray-500': criticalFlag === 'locked',
            'bg-red-300 text-red-500': criticalFlag === 'archived',
          }"
        >
          <template v-if="criticalFlag === 'locked'">
            {{ $t("locked by:") }} {{ quotation.locked_by.email }}
          </template>
          <template v-else-if="criticalFlag === 'archived'">
            {{ $t("archived") }}
          </template>
        </div>

        <!-- Details -->
        <div class="col-span-12 h-full sm:col-span-4 lg:col-span-3 2xl:col-span-2 print:col-span-3">
          <SkeletonLine v-if="isInitializing" class="h-full" />
          <OrderDetails
            v-else
            :quotation="quotation"
            :sales-id="id"
            @on-archive-order="handleArchiveOrder"
          />
        </div>

        <!-- Products -->
        <div
          class="col-span-12 flex flex-col overflow-visible sm:col-span-4 lg:col-span-6 2xl:col-span-8 print:col-span-9"
        >
          <SkeletonLine v-if="isInitializing" class="h-full" />
          <OrderProducts
            v-else
            :class="{
              'flex min-h-[calc(100vh_-_348px)] flex-col justify-center md:min-h-[calc(100vh_-_270px)]':
                !quotation.items.length,
            }"
            sales-type="order"
            :items="quotation.items"
            :items-price-array="quotation.items_price_array"
            :sales-id="id"
            :is-archived="quotation.archived"
            @on-product-added="handleProductAdded"
            @on-product-removed="handleProductRemoved"
            @on-product-updated="handleProductUpdated"
            @on-open-product-added="handleOpenProductAdded"
          />
          <hr class="mb-2 mt-6" />
          <SkeletonLine v-if="isInitializing" class="h-8" />
          <OrderServices
            v-else-if="
              (isEditable && permissions.includes('orders-update')) || quotation.services.length
            "
            :services="quotation.services"
            :quotation="quotation"
            :sales-id="id"
            @on-service-added="handleServiceAdded"
            @on-service-updated="handleServiceUpdated"
            @on-service-removed="handleServiceRemoved"
          />
        </div>

        <!-- Price & Actions -->
        <div class="col-span-12 sm:col-span-4 lg:col-span-3 2xl:col-span-2 print:hidden">
          <SkeletonLine v-if="isInitializing" class="h-full" />
          <div
            v-else-if="quotation.items.length || quotation.services.length"
            class="sticky top-0 flex h-[calc(100vh_-_64px_-_85px)] flex-col gap-4"
          >
            <SalesPriceSummary
              class="flex-1 overflow-y-auto"
              :items="quotation.items"
              :services="quotation.services"
              :subtotal="quotation.display_subTotal_price"
              :shipping-costs="quotation.display_shipping_cost"
              :vat="reducedVatArray"
              :total-vat="quotation.display_vat_price"
              :total="quotation.display_total_price"
            />
            <OrderActions
              v-if="!isInitializing"
              class="print:hidden"
              :status="quotation.status.code"
              :is-archived="quotation.archived"
              :is-internal="
                !(
                  quotation.items.find((item) => item.product.connection) &&
                  quotation.created_from == 'system'
                )
              "
              :show-done-button="
                quotation.status.code === statusMap.NEW &&
                quotation.items.length === 0 &&
                quotation.services.length > 0
              "
              :show-preview-invoice-button="quotation.has_transaction"
              @on-preview-invoice="handlePreviewInvoice"
              @on-generate-invoice="handleGenerateInvoice"
              @on-finalize-order="() => changeStatus(statusMap.NEW)"
              @on-done-editing="handleToggleEdit(false)"
              @on-edit-quotation="handleToggleEdit(true)"
              @on-archive-order="handleArchive"
              @on-set-order-to-done="handleSetOrderToDone"
              @on-dev-edit-status="(statusCode) => changeStatus(statusCode)"
            />
          </div>
        </div>
      </div>
    </div>
  </main>
  <SalesErrorPage
    v-else
    :error="error"
    sales-type="quotation"
    @reset-error="((error = null), navigateTo('/orders'))"
    @clear-error="error = null"
  />
</template>

<script setup>
import { computed } from "vue";
import { useStore } from "vuex";

const route = useRoute();
const id = route.params.id;

const { t: $t } = useI18n();

const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const { statusMap, isStatusInGroup } = useOrderStatus();
const { confirm } = useConfirmation();

const { theUser, permissions } = storeToRefs(useAuthStore());
const {
  changed,
  saving,
  criticalFlag,
  isEditable,
  isExternal,
  isHaveExternalItem,
  salesContext,
  pickupAddresses,
  leaving,
} = storeToRefs(useSalesStore());
const salesStore = useSalesStore();

const orderRepository = useOrderRepository();
const invoiceRepository = useInvoiceRepository();

const isInitializing = ref(true);

const showInvoicePreview = ref(false);

const quotation = ref(null);
const quotationMeta = ref(null);
const store = useStore();
const me = computed(() => store.state.settings.me);

function reduceVatArray(items_price_array, services_price_array) {
  const vatMap = new Map();

  // Process items
  items_price_array.forEach((item) => {
    const percentage = item.vat.vat_percentage;
    const amount = parseFloat(item.vat.vat) || 0;

    if (vatMap.has(percentage)) {
      vatMap.set(percentage, vatMap.get(percentage) + amount);
    } else {
      vatMap.set(percentage, amount);
    }
  });

  // Process services
  services_price_array.forEach((service) => {
    const percentage = service.vat.vat_percentage;
    const amount = parseFloat(service.vat.vat) || 0;

    if (vatMap.has(percentage)) {
      vatMap.set(percentage, vatMap.get(percentage) + amount);
    } else {
      vatMap.set(percentage, amount);
    }
  });

  const { formatCurrency } = useMoney();

  // Convert map to array of objects
  return Array.from(vatMap.entries()).map(([vat_percentage, amount]) => ({
    vat_percentage,
    total_vat_display: formatCurrency(amount * 100),
  }));
}

const reducedVatArray = computed(() =>
  reduceVatArray(quotation.value.items_price_array, quotation.value.services_price_array),
);

const outlineStyle = computed(() => {
  if (criticalFlag.value) {
    return {
      outlineWidth: "8px",
      outlineStyle: "solid",
    };
  } else {
    return {};
  }
});

onMounted(async () => {
  await getOrder();
  window.addEventListener("refresh-orders", getOrder);
});

onBeforeUnmount(() => {
  leaving.value = true;
  salesStore.reset();
  window.removeEventListener("refresh-orders", getOrder);
});

function setWarnings() {
  if (!quotation.value.items.length && !quotation.value.services.length) {
    salesStore.addWarning("items", $t("please add products or services to this order"));
  } else {
    salesStore.removeWarning("items");
  }
}

const error = ref(null);
onErrorCaptured((err, info) => {
  error.value = {
    statusCode: 500,
    message: err.message,
  };
  console.error(err, info);
  return false; // Prevent the error from propagating further
});

const abortController = ref(null);
async function getOrder(withoutInit = false) {
  store.commit(`fm/content/emptyFiles`);
  if (abortController.value) {
    abortController.value.abort("Abort previous request");
  }
  abortController.value = new AbortController();

  if (!permissions.value.includes("orders-read")) {
    addToast({
      type: "error",
      message: $t("You do not have permission to view orders."),
    });
    navigateTo("/orders");
    return;
  }
  try {
    if (!withoutInit) isInitializing.value = true;
    const [resData, resMeta] = await orderRepository.getOrderById(id, {
      signal: abortController.value.signal,
    });
    await nextTick();

    quotation.value = resData;
    quotationMeta.value = resMeta;

    salesContext.value = resData.context?.id ?? false;

    isExternal.value = resData.external_connection;

    if (resData.locked_by && resData.locked_by?.id !== theUser.value?.id) {
      criticalFlag.value = "locked";
    } else if (resData.archived) {
      criticalFlag.value = "archived";
    } else {
      criticalFlag.value = false;
    }

    let attachments = quotation.value.attachments;
    quotation.value.items.forEach((item) => {
      attachments.push(...item.attachments);
    });
    attachments = attachments.map((path) => {
      return {
        path: path.path.startsWith("/") ? path.path.slice(1).replace(/\/{2,}/g, "/") : path.path.replace(/\/{2,}/g, "/"),
      };
    });
    store.commit(`fm/content/setFiles`, attachments);

    isEditable.value =
      resData.status &&
      isStatusInGroup(resData.status.code, "EDITING") &&
      criticalFlag.value !== "locked" &&
      criticalFlag.value !== "archived";
    isHaveExternalItem.value =
      !!resData?.items.find((item) => {
        return (
          item.product.external_id !== me.value.tenant_id &&
          item.status.code !== statusMap.NEW &&
          item.status.code !== statusMap.DRAFT &&
          item.status.code !== statusMap.CANCELED
        );
      }) &&
      resData.customer &&
      resData.delivery_address;
  } catch (error) {
    if (error.message.includes("Abort previous request")) return;
    handleError(error);
  } finally {
    if (!withoutInit) isInitializing.value = false;
    setWarnings();
  }
}

async function handleArchiveOrder() {
  try {
    saving.value = true;
    await confirm({
      title: $t("archive order"),
      message: $t("are you sure you want to archive this order?"),
      confirmOptions: {
        label: $t("archive order"),
        variant: "theme",
      },
    });
    await orderRepository.setArchived(id, true);
    addToast({
      type: "success",
      message: $t("the order has been successfully archived."),
    });
    navigateTo({ path: "/orders", query: { tab: "archive" } });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

function handlePreviewInvoice() {
  showInvoicePreview.value = true;
}

async function handleGenerateInvoice() {
  try {
    await confirm({
      title: $t("Generate Invoice"),
      // eslint-disable-next-line prettier/prettier
      message: $t("Are you sure you want to generate an invoice from this order? This action cannot be undone."),
      confirmOptions: {
        label: $t("Generate Invoice"),
        variant: "theme",
      },
    });
    saving.value = true;
    await invoiceRepository.createInvoiceFromOrder(id);
    addToast({
      type: "success",
      message: $t("The invoice has been successfully generated."),
    });
    // Refetch quotation to get the invoice
    getOrder(true);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

function handleProductAdded(newProduct) {
  quotation.value = { ...quotation.value, items: [...quotation.value.items, newProduct] };
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleProductUpdated() {
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleProductRemoved(productId) {
  quotation.value.items = quotation.value.items.filter((item) => item.id !== productId);
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleOpenProductAdded(newProduct) {
  quotation.value = { ...quotation.value, items: [...quotation.value.items, newProduct] };
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleServiceAdded(newService) {
  quotation.value.services.push(newService);
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleServiceUpdated(updatedService) {
  const serviceIndex = quotation.value.services.findIndex(
    (service) => service.id === updatedService.id,
  );
  quotation.value.services[serviceIndex] = updatedService;
  // Refetch quotation to get the correct prices
  getOrder(true);
}

function handleServiceRemoved(serviceId) {
  quotation.value.services = quotation.value.services.filter((service) => service.id !== serviceId);
  // Refetch quotation to get the correct prices
  getOrder(true);
}

async function changeStatus(status) {
  saving.value = true;
  try {
    await orderRepository.updateOrder(id, { st: status });
    await getOrder(true);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleSetOrderToDone() {
  try {
    await confirm({
      title: $t("set order to done"),
      message: $t("are you sure you want to set this order to done?"),
      confirmOptions: {
        label: $t("set to done"),
        variant: "success",
      },
    });
    await changeStatus(statusMap.DONE);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  }
}

async function handleToggleEdit(editing) {
  const payload = {
    editing,
  };

  if (quotation.value.status.code === statusMap.WAITING_FOR_RESPONSE) {
    payload.st = statusMap.NEW;
  }

  try {
    saving.value = true;
    await orderRepository.updateOrder(id, payload);
    await getOrder(true);
  } catch (err) {
    handleError(err);
  } finally {
    saving.value = false;
  }
}

async function handleArchive() {
  try {
    saving.value = true;
    await confirm({
      title: $t("archive order"),
      message: $t("are you sure you want to archive this order?"),
      confirmOptions: {
        label: $t("archive"),
        variant: "danger",
      },
    });
    orderRepository.setArchived(quotation.value.id, true);
    await getOrder(true);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

function handleCloseOrder() {
  if (quotation.value.archived) {
    return navigateTo({ path: "/orders", query: { tab: "archive" } });
  } else if (quotation.value.status.code === statusMap.DRAFT) {
    return navigateTo({ path: "/orders", query: { tab: "drafts" } });
  } else {
    return navigateTo("/orders");
  }
}

watch(
  () => salesContext.value,
  async () => {
    if (salesContext.value && permissions.value.includes("members-access")) {
      pickupAddresses.value = await orderRepository.getPickupAddresses(salesContext.value);
    }
  },
  { immediate: true },
);

onBeforeRouteLeave((_, __, next) => {
  if (changed.value) {
    addToast({
      type: "info",
      // prettier-ignore
      message: $t("Please wait {seconds} seconds until we've saved your changes.", { seconds: Math.ceil((5000 - (Date.now() - changed.value)) / 1000) }),
    });
    return next(false);
  }

  if (saving.value) {
    addToast({
      type: "error",
      message: $t("please wait until the changes are saved"),
    });
    return next(false);
  }
  leaving.value = true;

  salesStore.reset();
  next();
});
</script>

<style lang="scss" scoped>
.quotation-grid {
  grid-template-areas: "details products prices";
}
</style>
