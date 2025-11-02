<template>
  <div>
    <InvoiceModalPreview
      :show="!!showInvoicePreview"
      :invoice="showInvoicePreview"
      :order-id="showInvoicePreview ? showInvoicePreview.order_id : 0"
      @on-close="showInvoicePreview = false"
      @on-backdrop-click="showInvoicePreview = false"
    />
    <header class="container relative flex items-center justify-between p-4 py-3">
      <StudioQuickButton to="/manage/studio/invoice" />
      <div class="absolute left-1/2 flex -translate-x-1/2 items-center text-lg">
        <font-awesome-icon :icon="['fal', 'money-bill-trend-up']" class="mb-1 mr-2" />
        <h1>{{ $t("Finances") }}</h1>
      </div>
      <div>
        <UIButton to="/manage/settings#invoice" variant="link" :icon="['fal', 'gear']">
          {{ $t("Settings") }}
        </UIButton>
      </div>
    </header>
    <div class="container grid grid-cols-12 gap-6 p-4 pt-0">
      <main class="col-span-12 -mt-[1px] grid grid-cols-12 gap-6 2xl:col-span-12">
        <div v-if="permissions.includes('transactions-list')" class="col-span-12">
          <FinancesTable
            :loading="loading"
            :data="invoices"
            :pagination="pagination"
            :filter="searchQuery"
            :column-filters="columnFilters"
            :get-invoices="handlePaginationChange"
            hover
            @row-click="handleRowClick"
            @page-change="handlePaginationChange(20, $event)"
          />
          <template v-if="invoices.length === 0">
            <UICard class="h-full pt-4">
              <ZeroState :message="$t('No invoices found')" />
            </UICard>
          </template>
        </div>
        <div v-else class="col-span-12">
          <UICardHeader>
            <template #left>
              <UICardHeaderTitle :title="$t('Invoices')" :icon="['fal', 'file-invoice-dollar']" />
            </template>
          </UICardHeader>
          <NoPermissions
            :message="$t('You do not have permission to view invoices.')"
            class="py-4"
          />
        </div>
      </main>
      <!--      <aside class="col-span-3 2xl:col-span-3">-->
      <!--        <UICardHeader>-->
      <!--          <template #left>-->
      <!--            <UICardHeaderTitle :title="$t('Filter')" />-->
      <!--          </template>-->
      <!--        </UICardHeader>-->
      <!--        <UICard class="!bg-transparent px-2 py-4 !shadow-none">-->
      <!--          <div class="mb-4">-->
      <!--            <h3 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">-->
      <!--              {{ $t("Search") }}-->
      <!--            </h3>-->
      <!--            <UIInputText-->
      <!--              v-model="searchQuery"-->
      <!--              name="search"-->
      <!--              :prefix="['fal', 'search']"-->
      <!--              :placeholder="$t('Search')"-->
      <!--            />-->
      <!--          </div>-->
      <!--          <div class="mb-4 flex flex-wrap gap-4">-->
      <!--            <div class="flex-1">-->
      <!--              <label-->
      <!--                for="start-date"-->
      <!--                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"-->
      <!--              >-->
      <!--                {{ $t("Start Date") }}-->
      <!--              </label>-->
      <!--              <input-->
      <!--                id="start-date"-->
      <!--                v-model="dueDateFilter.start"-->
      <!--                type="date"-->
      <!--                class="input py-1 font-normal"-->
      <!--              />-->
      <!--            </div>-->
      <!--            <div class="flex-1">-->
      <!--              <label-->
      <!--                for="end-date"-->
      <!--                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"-->
      <!--              >-->
      <!--                {{ $t("End Date") }}-->
      <!--              </label>-->
      <!--              <input-->
      <!--                id="end-date"-->
      <!--                v-model="dueDateFilter.end"-->
      <!--                type="date"-->
      <!--                class="input py-1 font-normal"-->
      <!--              />-->
      <!--            </div>-->
      <!--          </div>-->
      <!--          <div class="mb-4">-->
      <!--            <h3 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">-->
      <!--              {{ $t("Type") }}-->
      <!--            </h3>-->
      <!--            <div class="flex">-->
      <!--              <UIButton-->
      <!--                :icon="['fal', 'list-dropdown']"-->
      <!--                class="flex-1 rounded-none rounded-l border-b border-l border-t border-gray-200 py-3 hover:border-theme-300"-->
      <!--                :class="{-->
      <!--                  'border-theme-300 !bg-theme-200 text-white hover:!bg-theme-300 dark:border-theme-500 dark:!bg-theme-500 dark:hover:!bg-theme-500':-->
      <!--                    typeQuery.includes('single'),-->
      <!--                }"-->
      <!--                @click="toggleTypeQuery('single')"-->
      <!--              >-->
      <!--                {{ $t("Single") }}-->
      <!--              </UIButton>-->
      <!--              <UIButton-->
      <!--                :icon="['fal', 'table-list']"-->
      <!--                class="flex-1 rounded-none border-b border-l border-r border-t border-gray-200 py-3 hover:border-theme-300"-->
      <!--                :class="{-->
      <!--                  'border-theme-300 !bg-theme-200 text-white hover:!bg-theme-300 dark:border-theme-500 dark:!bg-theme-500 dark:hover:!bg-theme-500':-->
      <!--                    typeQuery.includes('splitted'),-->
      <!--                }"-->
      <!--                @click="toggleTypeQuery('splitted')"-->
      <!--              >-->
      <!--                {{ $t("Splitted") }}-->
      <!--              </UIButton>-->
      <!--              <UIButton-->
      <!--                :icon="['fal', 'bars']"-->
      <!--                class="flex-1 rounded-none rounded-r border-b border-r border-t border-gray-200 py-3 hover:border-theme-300"-->
      <!--                :class="{-->
      <!--                  'border-theme-300 !bg-theme-200 text-white hover:!bg-theme-300 dark:border-theme-500 dark:!bg-theme-500 dark:hover:!bg-theme-500':-->
      <!--                    typeQuery.includes('credit note'),-->
      <!--                }"-->
      <!--                @click="toggleTypeQuery('credit note')"-->
      <!--              >-->
      <!--                {{ $t("Credit Note") }}-->
      <!--              </UIButton>-->
      <!--            </div>-->
      <!--          </div>-->
      <!--          <div class="mb-4">-->
      <!--            <h3-->
      <!--              class="mb-2 flex justify-between text-sm font-medium text-gray-700 dark:text-gray-200"-->
      <!--            >-->
      <!--              {{ $t("Status") }}-->
      <!--            </h3>-->
      <!--            <ul>-->
      <!--              <li>-->
      <!--                <label-->
      <!--                  for="paid"-->
      <!--                  class="-mx-2 flex cursor-pointer items-center justify-between rounded px-2 py-3 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-900"-->
      <!--                >-->
      <!--                  <div class="flex items-center gap-1">-->
      <!--                    <input-->
      <!--                      id="paid"-->
      <!--                      type="checkbox"-->
      <!--                      class="h-4 w-4 border-gray-300 text-indigo-500 focus:ring-indigo-500"-->
      <!--                      :checked="statusQuery.includes('paid')"-->
      <!--                      @change="toggleStatusQuery('paid')"-->
      <!--                    />-->
      <!--                    <span>{{ $t("Paid") }}</span>-->
      <!--                  </div>-->
      <!--                </label>-->
      <!--              </li>-->
      <!--              <li>-->
      <!--                <label-->
      <!--                  for="unpaid"-->
      <!--                  class="-mx-2 flex cursor-pointer items-center justify-between rounded px-2 py-3 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-900"-->
      <!--                >-->
      <!--                  <div class="flex items-center gap-1">-->
      <!--                    <input-->
      <!--                      id="unpaid"-->
      <!--                      type="checkbox"-->
      <!--                      class="h-4 w-4 border-gray-300 text-indigo-500 focus:ring-indigo-500"-->
      <!--                      :checked="statusQuery.includes('unpaid')"-->
      <!--                      @change="toggleStatusQuery('unpaid')"-->
      <!--                    />-->
      <!--                    <span>{{ $t("Unpaid") }}</span>-->
      <!--                  </div>-->
      <!--                </label>-->
      <!--              </li>-->
      <!--              <li>-->
      <!--                <label-->
      <!--                  for="overdue"-->
      <!--                  class="-mx-2 flex cursor-pointer items-center justify-between rounded px-2 py-3 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-900"-->
      <!--                >-->
      <!--                  <div class="flex items-center gap-1">-->
      <!--                    <input-->
      <!--                      id="overdue"-->
      <!--                      type="checkbox"-->
      <!--                      class="h-4 w-4 border-gray-300 text-indigo-500 focus:ring-indigo-500"-->
      <!--                      :checked="statusQuery.includes('overdue')"-->
      <!--                      @change="toggleStatusQuery('overdue')"-->
      <!--                    />-->
      <!--                    <span>{{ $t("Overdue") }}</span>-->
      <!--                  </div>-->
      <!--                </label>-->
      <!--              </li>-->
      <!--            </ul>-->
      <!--          </div>-->
      <!--          &lt;!&ndash; <SalesActionButton variant="primary">{{ $t("Apply Filter") }}</SalesActionButton> &ndash;&gt;-->
      <!--        </UICard>-->
      <!--      </aside>-->
    </div>
  </div>
</template>

<script setup>
// import { format } from "date-fns";
// import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
// import { vTooltip } from "floating-vue";
import FinancesTable from "~/components/finances/FinancesTable.vue";

const { t: $t } = useI18n();
const { handleError } = useMessageHandler();
const financeRepository = useFinanceRepository();
const { permissions } = storeToRefs(useAuthStore());

const loading = ref(false);
const showInvoicePreview = ref(false);
const searchQuery = ref("");
const statusQuery = ref(["paid", "unpaid", "overdue"]);
const typeQuery = ref(["single", "splitted", "credit note"]);

const invoices = ref([]);
const pagination = ref({
  pageIndex: 1,
  pageSize: 10,
});
const columnFilters = ref([]);

async function handlePaginationChange(perPage, page, searchQuery = "") {
  try {
    loading.value = true;

    const [data, meta] = await financeRepository.getAllInvoices({
      // perPage: pagination.value.pageSize,
      perPage: perPage,
      page: page,
    },searchQuery);
    invoices.value = data;
    pagination.value = meta;
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function handleRowClick(row) {
  showInvoicePreview.value = row;
}

function setColumnFilters(accessorKey, filterValue) {
  const columnFiltersWithoutCurrent = columnFilters.value.filter(
    (filter) => filter.id !== accessorKey,
  );
  columnFilters.value = [
    ...columnFiltersWithoutCurrent,
    {
      id: accessorKey,
      value: filterValue,
    },
  ];
}

watch(statusQuery, (value) => setColumnFilters("paymentStatus", value), {
  immediate: true,
  deep: true,
});
watch(typeQuery, (value) => setColumnFilters("type", value), { immediate: true, deep: true });

const dueDateFilter = ref({
  start: null,
  end: null,
});
watch(dueDateFilter, (value) => setColumnFilters("dueDate", value), {
  immediate: true,
  deep: true,
});

onMounted(async () => await handlePaginationChange(20, 1));
</script>
