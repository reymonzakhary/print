<template>
  <main class="p-4" :class="{ container: activeView === 'list' }">
    <VTour ref="quotationsUpdateTour" name="quotationsUpdateTour" :steps="steps" highlight />
    <QuotationsNav
      :active-type="activeTab"
      @update:active-type="activeTab = $event"
      @start-tour="startTour"
    />
    <div class="sticky top-0 z-10">
      <!-- table actions -->
      <UICardHeader data-step="quotations-controls">
        <template #left>
          <UIButton
            v-if="permissions.includes('quotations-create')"
            :icon="['fal', 'plus']"
            data-step="create-quotation"
            @click="handleCreateQuotation"
          >
            {{ $t("create new quotation") }}
          </UIButton>
        </template>

        <template #center>
          <UICardHeaderPagination
            :loading="loading"
            :last-page="pagination.lastPage"
            :page="pagination.page"
            :per-page="pagination.perPage"
            data-step="pagination"
            @update:page="handlePageChange"
            @update:per-page="handlePerPageChange"
          />
        </template>

        <template #right>
          <SalesViewToggleButtons
            :active-view="activeView"
            data-step="view-toggle"
            @update:active-view="activeView = $event"
          />
        </template>
      </UICardHeader>

      <!-- serchbar -->
      <section
        class="flex w-full justify-center bg-theme-50 p-2 text-center backdrop-blur-md dark:bg-theme-700"
        data-step="search-bar"
      >
        <UIInputText
          type="text"
          class="relative mx-auto w-full rounded border border-theme-400 bg-white text-sm text-gray-800 transition-all focus:border-theme-500 focus:outline-none focus:ring focus:ring-theme-200 dark:bg-gray-700 dark:text-white lg:w-1/3"
          name="search"
          :placeholder="$t('Search quotations')"
          :icon="['fal', 'magnifying-glass']"
          :icon-position="'right'"
          @input="debounceSearch"
        />
      </section>
    </div>
    <div v-if="!permissions.includes('quotations-list')" class="grid h-full place-items-center">
      <NoPermissions :message="$t('You have no permission to view a list of quotations.')" />
    </div>
    <div v-if="quotations.length <= 0 && !loading" class="grid h-full place-items-center">
      <ZeroState :message="$t('We\'re sorry, it seems like there are no quotations found.')" />
    </div>
    <UICard
      v-else-if="!loading && permissions.includes('quotations-list')"
      class="!py-0"
      data-step="quotations-table"
    >
      <div
        class="grid"
        :class="{
          'grid-cols-1': activeView === 'items-only',
          'grid-cols-2': activeView === 'grid',
        }"
      >
        <div>
          <QuotationsTable
            v-if="activeView === 'grid' || activeView === 'list'"
            :is-bin="activeTab === 'bin'"
            :active-view="activeView"
            :sort-data="sortData"
            :quotations="quotations"
            :hovered-quotation="hoveredQuotation"
            @update:hovered-quotation="handleQuotationHover"
            @update:sort="handleSort"
            @on-delete-quotation="handleDeleteQuotation"
            @cancel-item="handleCancelItem"
          />
        </div>
        <div>
          <QuotationsItemsTable
            v-if="activeView !== 'list'"
            :active-view="activeView"
            :quotations="quotations"
            :is-bin="activeTab === 'bin'"
            :hovered-quotation="hoveredQuotation"
            @update:hovered-quotation="handleQuotationHover"
            @on-cancel-item="handleCancelItem"
            @on-update-item-status="handleUpdateItemStatus"
          />
        </div>
      </div>
    </UICard>
    <template v-else-if="loading">
      <skeletonLine :height="12" />
      <skeletonLine :height="12" />
      <skeletonLine :height="12" />
      <skeletonLine :height="12" />
      <skeletonLine :height="12" />
      <skeletonLine :height="12" />
    </template>
    <QuotationsPlaceholder
      v-else-if="quotations.length <= 0 && !loading"
      @on-create="handleCreateQuotation"
    />
  </main>
</template>

<script setup>
const route = useRoute();
const router = useRouter();

const { t: $t } = useI18n();

const { permissions } = storeToRefs(useAuthStore());
const { saving } = storeToRefs(useSalesStore());
const { addToast } = useToastStore();

const quotationRepository = useQuotationRepository();
const { handleError } = useMessageHandler();
const { statusMap } = useOrderStatus();
const { confirm } = useConfirmation();

// data
const loading = ref(true);
const quotationsUpdateTour = ref(null);
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
  (newVal) => localStorage.setItem("quotationPerPage", JSON.stringify(newVal.perPage)),
  { deep: true },
);

// computed
const activeTab = computed({
  get: () => route.query.tab || "quotations",
  set: (value) => {
    pagination.value.page = 1;
    router.push({ query: { ...route.query, tab: value } });
    if (value === "quotations") return getQuotations({ status: `${allStatusesMinusDrafts}` });
    if (value === "drafts") return getQuotations({ status: 300 });
    if (value === "bin") return getQuotations({ trashed: true });
    return getQuotations();
  },
});

const activeView = computed({
  get: () => route.query.view || "grid",
  set: (value) => {
    localStorage.setItem("quotationView", value);
    router.push({ query: { ...route.query, view: value } });
  },
});

// methods
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
  trashed = false,
  sort = "created_at",
  direction = "desc",
  search = null,
} = {}) {
  if (!permissions.value.includes("quotations-list")) {
    addToast({
      type: "error",
      message: $t("You do not have permission to view quotations."),
    });
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const [data, meta] = await quotationRepository.getAllQuotations({
      page: pagination.value.page,
      perPage: pagination.value.perPage,
      sortBy: sort,
      sortOrder: direction,
      search: search,
      status,
      trashed,
    });

    quotations.value = data;
    pagination.value.page = meta.current_page;
    pagination.value.perPage = meta.per_page;
    pagination.value.lastPage = meta.last_page;
  } catch (error) {
    quotations.value = [];
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function handleDeleteQuotation(id) {
  try {
    saving.value = true;
    await confirm({
      title: $t("delete quotation"),
      message: $t("are you sure you want to delete this quotation?"),
      confirmOptions: {
        label: $t("delete"),
        variant: "danger",
      },
    });
    await quotationRepository.deleteQuotation(id);
    const filteredQuotations = quotations.value.filter((quotation) => quotation.id !== id);
    quotations.value = filteredQuotations;
    addToast({
      type: "success",
      message: $t("the quotation has been successfully deleted."),
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
  if (activeTab.value === "quotations") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (activeTab.value === "bin") {
    return getQuotations({ trashed: true });
  } else {
    return getQuotations();
  }
}

async function handlePerPageChange(perPage) {
  pagination.value.perPage = Number(perPage);
  if (activeTab.value === "quotations") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (activeTab.value === "bin") {
    return getQuotations({ trashed: true });
  } else {
    return getQuotations();
  }
}

async function handleSort(e) {
  sortData.value = e;
  if (activeTab.value === "quotations") {
    return await getQuotations({
      status: `${allStatusesMinusDrafts}`,
      sort: e.field,
      direction: e.direction,
    });
  } else if (activeTab.value === "drafts") {
    return await getQuotations({ status: 300, sort: e.field, direction: e.direction });
  } else if (activeTab.value === "bin") {
    return getQuotations({ trashed: true, sort: e.field, direction: e.direction });
  } else {
    return getQuotations({ sort: e.field, direction: e.direction });
  }
}

async function handleCreateQuotation() {
  try {
    saving.value = true;
    const quotation = await quotationRepository.createQuotation();
    navigateTo(`/quotations/${quotation.id}`);
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
    await quotationRepository.updateItem({
      quotationId,
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
  quotationsUpdateTour.value.resetTour();
  quotationsUpdateTour.value.startTour();
};

async function handleUpdateItemStatus({ quotationId, itemId, status }) {
  try {
    saving.value = true;
    await quotationRepository.updateItem({ quotationId, itemId, data: { st: status } });
    const quotationIndex = quotations.value.findIndex((quotation) => quotation.id === quotationId);
    const itemIndex = quotations.value[quotationIndex].items.findIndex(
      (item) => item.id === itemId,
    );
    quotations.value[quotationIndex].items[itemIndex].status.code = status;
    addToast({
      type: "success",
      message: $t("the item status has been successfully updated."),
    });
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = true;
  }
}

onBeforeMount(() => {
  if (!permissions.value.includes("quotations-access")) {
    return router.push({ name: "index" });
  }
});

onMounted(async () => {
  const savedPagination = localStorage.getItem("quotationPerPage");
  if (savedPagination) {
    const parsedPagination = JSON.parse(savedPagination);
    pagination.value = { ...pagination.value, perPage: parsedPagination };
  }
  const savedView = localStorage.getItem("quotationView");

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

  if (query.tab === "quotations") {
    return await getQuotations({ status: `${allStatusesMinusDrafts}` });
  } else if (query.tab === "drafts") {
    return await getQuotations({ status: 300 });
  } else if (query.tab === "bin") {
    return await getQuotations({ trashed: true });
  } else {
    return getQuotations();
  }
});

const steps = ref([
  {
    target: "#sidebarmenu [href='/manager/quotations']",
    title: $t("Welcome to Quotations Management"),
    body: $t(
      "This is your central hub for managing all quotations. Here you can create new quotations, track their progress, and manage the entire quotation lifecycle from draft to completion.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-v-step="1"]',
    title: $t("Active Quotations"),
    body: $t(
      "This tab displays all your active quotations that are no longer in draft status. These are quotations that have been sent to clients or are in various stages of the sales process.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-v-step="2"]',
    title: $t("Draft Quotations"),
    body: $t(
      "This section contains all your draft quotations that are still being worked on. Use this area to continue editing quotations before sending them to clients. Drafts are kept separate to maintain a clean overview of your active quotations.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-v-step="3"]',
    title: $t("Deleted Quotations (Bin)"),
    body: $t(
      "Here you'll find all deleted quotations. Don't worry - nothing is permanently lost! You can restore quotations from the bin or permanently delete them when you're sure you no longer need them.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-step="create-quotation"]',
    title: $t("Create New Quotation"),
    body: $t(
      "Click this button to quickly create a new quotation. You'll be taken to the quotation editor where you can add products, set pricing, and configure all quotation details.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-step="search-bar"]',
    title: $t("Search Quotations"),
    body: $t(
      "Use this search bar to quickly find specific quotations by customer name, quotation number, or any other relevant information. The search works across all quotation data.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-step="view-toggle"]',
    title: $t("View Options"),
    body: $t(
      "Switch between different view modes: List view for a compact overview, Grid view for a balanced display of quotations and items, or Items-only view to focus on individual quotation items.",
    ),
    popperConfig: {
      placement: "left",
    },
  },
  {
    target: '[data-step="pagination"]',
    title: $t("Navigation & Display"),
    body: $t(
      "Control how many quotations are displayed per page and navigate through multiple pages. You can adjust the number of items shown to match your workflow preferences.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
  {
    target: '[data-step="quotations-table"]',
    title: $t("Quotations Overview"),
    body: $t(
      "This table provides a comprehensive overview of your quotations. You can sort by different columns, view quotation status, customer information, and quickly access individual quotations for editing or review.",
    ),
    popperConfig: {
      placement: "top",
    },
  },
  {
    target: '[data-step="quotations-settings"]',
    title: $t("Quotations Settings"),
    body: $t(
      "Access quotation-specific settings here. Configure default quotation templates, pricing rules, approval workflows, and other quotation-related preferences to streamline your quotation process.",
    ),
    popperConfig: {
      placement: "bottom",
    },
  },
]);
</script>
