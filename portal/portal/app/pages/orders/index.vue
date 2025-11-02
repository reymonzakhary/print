<template>
  <main class="p-4" :class="{ container: activeView === 'list' }">
    <VTour ref="ordersUpdateTour" name="ordersUpdateTour" :steps="steps" />
    <OrdersNav :active-type="activeTab" @update:active-type="activeTab = $event" />
    <div class="sticky top-0 z-10">
      <UICardHeader>
        <template #left>
          <UIButton
            v-if="permissions.includes('orders-create')"
            :icon="['fal', 'plus']"
            @click="handleCreateQuotation"
          >
            {{ $t("create new order") }}
          </UIButton>
        </template>

        <template #center>
          <UICardHeaderPagination
            :loading="loading"
            :last-page="pagination.lastPage"
            :page="pagination.page"
            :per-page="pagination.perPage"
            @update:page="handlePageChange"
            @update:per-page="handlePerPageChange"
          />
        </template>

        <template #right>
          <SalesViewToggleButtons
            :active-view="activeView"
            @update:active-view="activeView = $event"
          />
        </template>
      </UICardHeader>

      <!-- serchbar -->
      <section
        class="flex w-full justify-center bg-theme-50 p-2 text-center backdrop-blur-md dark:bg-theme-700"
      >
        <UIInputText
          type="text"
          class="relative mx-auto w-full rounded border border-theme-400 bg-white text-sm text-gray-800 transition-all focus:border-theme-500 focus:outline-none focus:ring focus:ring-theme-200 dark:bg-gray-700 dark:text-white lg:w-1/3"
          name="search"
          :placeholder="$t('Search orders')"
          :icon="['fal', 'magnifying-glass']"
          :icon-position="'right'"
          @input="debounceSearch"
        />
      </section>
    </div>
    <UICard v-if="!loading" class="!py-0">
      <div
        class="grid"
        :class="{
          'grid-cols-1': activeView === 'items-only',
          'grid-cols-2': activeView === 'grid',
        }"
      >
        <div>
          <OrdersTable
            v-if="activeView === 'grid' || activeView === 'list'"
            :active-view="activeView"
            :sort-data="sortData"
            :quotations="quotations"
            :hovered-quotation="hoveredQuotation"
            @update:hovered-quotation="handleQuotationHover"
            @update:sort="handleSort"
            @on-archive-order="handleArchiveOrder"
            @on-cancel-item="handleCancelItem"
          />
        </div>
        <div>
          <OrdersItemsTable
            v-if="activeView !== 'list'"
            :active-view="activeView"
            :quotations="quotations"
            :hovered-quotation="hoveredQuotation"
            :disable-actions="activeTab === 'archive'"
            @update:hovered-quotation="handleQuotationHover"
            @on-produce-item="MakeProduceItem"
            @on-cancel-item="handleCancelItem"
            @on-update-item-status="handleUpdateItemStatus"
          />
        </div>
      </div>
    </UICard>
    <skeletonLine v-if="loading" :height="12" />
    <skeletonLine v-if="loading" :height="12" />
    <skeletonLine v-if="loading" :height="12" />
    <skeletonLine v-if="loading" :height="12" />
    <skeletonLine v-if="loading" :height="12" />
    <skeletonLine v-if="loading" :height="12" />
    <OrdersPlaceholder
      v-if="quotations.length <= 0 && !loading"
      @on-create="handleCreateQuotation"
    />
  </main>
</template>

<script setup>
const route = useRoute();
const router = useRouter();

const { t: $t } = useI18n();

const { permissions } = storeToRefs(useAuthStore());
const { handleError } = useMessageHandler();
const { confirm } = useConfirmation();
const { addToast } = useToastStore();
const api = useAPI();

const orderRepository = useOrderRepository();
const { saving } = storeToRefs(useSalesStore());
const { statusMap } = useOrderStatus();

const loading = ref(true);
const ordersUpdateTour = ref(null);
const quotations = ref([]);
const hoveredQuotation = ref(null);
const sortData = ref({
  field: "created_at",
  direction: "desc",
});

const allStatusesMinusDrafts = Object.values(statusMap)
  .filter((status) => status !== statusMap.DRAFT)
  .join(",");

const pagination = ref({
  page: 1,
  perPage: 25,
  lastPage: 1,
});

// watch
watch(
  pagination,
  (newVal) => localStorage.setItem("orderPerPage", JSON.stringify(newVal.perPage)),
  { deep: true },
);

// computed
const activeTab = computed({
  get: () => route.query.tab || "orders",
  set: (value) => {
    pagination.value.page = 1;
    router.push({ query: { ...route.query, tab: value } });
    if (value === "orders") return getQuotations({ status: `${allStatusesMinusDrafts}` });
    if (value === "drafts") return getQuotations({ status: 300 });
    if (value === "archive") return getQuotations({ archived: true });
    return getQuotations();
  },
});

const activeView = computed({
  get: () => route.query.view || "grid",
  set: (value) => {
    localStorage.setItem("orderView", value);
    router.push({ query: { ...route.query, view: value } });
  },
});

const debounceSearch = useDebounceFn((search) => {
  const query = search.target.value;
  if (activeTab.value === "orders") {
    return getQuotations({ status: `${allStatusesMinusDrafts}`, search: query });
  } else if (activeTab.value === "drafts") {
    return getQuotations({ status: 300, search: query });
  } else if (activeTab.value === "archive") {
    return getQuotations({ archived: true, search: query });
  } else {
    return getQuotations({ search: query });
  }
}, 500);

async function getQuotations({
  status = null,
  archived = null,
  sort = "created_at",
  direction = "desc",
  search = null,
} = {}) {
  loading.value = true;
  try {
    const [data, meta] = await orderRepository.getAllOrders({
      page: pagination.value.page,
      perPage: pagination.value.perPage,
      sortBy: sort,
      sortOrder: direction,
      search: search,
      status,
      archived,
    });

    quotations.value = data;
    pagination.value.page = meta.current_page;
    pagination.value.perPage = meta.per_page;
    pagination.value.lastPage = meta.last_page;
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function handleArchiveOrder(id) {
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
    const filteredQuotations = quotations.value.filter((quotation) => quotation.id !== id);
    quotations.value = filteredQuotations;
    addToast({
      type: "success",
      message: $t("the order has been successfully archived."),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handlePageChange(page) {
  pagination.value.page = Number(page);
  if (activeTab.value === "orders") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (activeTab.value === "archive") {
    return getQuotations({ archived: true });
  } else {
    return getQuotations();
  }
}

async function handlePerPageChange(perPage) {
  pagination.value.perPage = Number(perPage);
  if (activeTab.value === "orders") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (activeTab.value === "archive") {
    return getQuotations({ archived: true });
  } else {
    return getQuotations();
  }
}

async function handleSort(e) {
  sortData.value = e;
  if (activeTab.value === "orders") {
    return await getQuotations({
      status: `${allStatusesMinusDrafts}`,
      sort: e.field,
      direction: e.direction,
    });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300, sort: e.field, direction: e.direction });
  } else {
    return getQuotations({ sort: e.field, direction: e.direction });
  }
}

async function handleCreateQuotation() {
  try {
    saving.value = true;
    const quotation = await orderRepository.createOrder();
    navigateTo(`/orders/${quotation.id}`);
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleCancelItem({ quotationId, itemId }) {
  try {
    saving.value = true;
    await confirm({
      title: $t("cancel item"),
      message: $t("are you sure you want to cancel this item?"),
      confirmOptions: {
        label: $t("cancel"),
        variant: "danger",
      },
    });
    await orderRepository.updateItem({
      orderId: quotationId,
      itemId,
      data: { st: statusMap.CANCELED },
    });
    const quotationIndex = quotations.value.findIndex((quotation) => quotation.id === quotationId);
    const itemIndex = quotations.value[quotationIndex].items.findIndex(
      (item) => item.id === itemId,
    );
    quotations.value[quotationIndex].items[itemIndex].status.code = statusMap.CANCELED;
    addToast({
      type: "success",
      message: $t("the item has been successfully canceled."),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

function handleQuotationHover(quotationId) {
  hoveredQuotation.value = quotationId;
}

const startTour = () => {
  ordersUpdateTour.value.resetTour();
  ordersUpdateTour.value.startTour();
};

async function handleUpdateItemStatus({ quotationId, itemId, status }) {
  try {
    saving.value = true;

    // Update the status of the item
    await orderRepository.updateItem({ orderId: quotationId, itemId, data: { st: status } });

    // Optimistically update the status of the item
    const quotationIndex = quotations.value.findIndex((quotation) => quotation.id === quotationId);
    const itemIndex = quotations.value[quotationIndex].items.findIndex((itm) => itm.id === itemId);
    quotations.value[quotationIndex].items[itemIndex].status.code = status;
    addToast({
      type: "success",
      message: $t("the item status has been successfully updated."),
    });

    // Get the status of the order to update the order status without reloading the whole page
    const [order] = await orderRepository.getOrderById(quotationId);
    quotations.value[quotationIndex].status.code = order.status.code;
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

const MakeProduceItem = async ({ quotationId, itemId, status, data }) => {
  try {
    saving.value = true;
    await api.post(`orders/${quotationId}/items/produce`, data);
    const quotationIndex = quotations.value.findIndex((quotation) => quotation.id === quotationId);
    // const itemIndex = quotations.value[quotationIndex].items.findIndex((itm) => itm.id === itemId);
    // quotations.value[quotationIndex].items[itemIndex].status.code = status;
    // Get the status of the order to update the order status without reloading the whole page

    const [order] = await orderRepository.getOrderById(quotationId);
    quotations.value[quotationIndex] = order;
    addToast({
      type: "success",
      message: $t("the item status has been successfully updated."),
    });
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  window.addEventListener("refresh-all-orders", refreshQuotations);
  const savedPagination = localStorage.getItem("orderPerPage");
  if (savedPagination) {
    const parsedPagination = JSON.parse(savedPagination);
    pagination.value = { ...pagination.value, perPage: parsedPagination };
  }
  const savedView = localStorage.getItem("orderView");

  const query = { ...route.query };
  let changed = false;

  if (!query.tab) {
    query.tab = activeTab.value;
    changed = true;
  }

  if (!query.view) {
    query.view = savedView || activeView.value;
    changed = true;
  }

  if (changed) {
    router.replace({ query });
  }

  if (query.tab === "orders") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (query.tab === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (query.tab === "archive") {
    return await getQuotations({ archived: true });
  } else {
    return await getQuotations();
  }

  // window.addEventListener("refresh-all-orders", refreshQuotations);
});

onBeforeUnmount(() => {
  window.removeEventListener("refresh-all-orders", refreshQuotations);
});

const refreshQuotations = async () => {
  const query = { ...route.query };

  if (!query.tab) {
    query.tab = activeTab.value;
    changed = true;
  }
  const savedView = localStorage.getItem("orderView");

  if (!query.view) {
    query.view = savedView || activeView.value;
    changed = true;
  }

  if (query.tab === "orders") {
    await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (query.tab === "drafts") {
    await getQuotations({ status: 300 });
  } else if (query.tab === "archive") {
    await getQuotations({ archived: true });
  } else {
    await getQuotations();
  }
};

const steps = ref([
  {
    target: '[data-v-step="1"]',
    title: "Welcome to the renewed quotations page!",
    //prettier-ignore
    body: $t("We've made some changes to make your experience even better. Starting with the tidier grid view."),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-v-step="2"]',
    //prettier-ignore
    body: $t("From now on your drafts are displayed here. While we keep your last 3 drafts in the main quotations view. From the drafts tab, you can easily access all of them. Keeping your quotations neet and tidy."),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-v-step="3"]',
    //prettier-ignore
    body: $t("Even though you could delete your quotations, there was no way to restore them. Now you can find all your deleted quotations here. You can restore them or delete them permanently."),
    popperConfig: {
      placement: "bottom",
    },
  },
]);
</script>
