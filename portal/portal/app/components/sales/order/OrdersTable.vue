<template>
  <Teleport to="body">
    <SalesJobTicket
      v-if="showJobTicket"
      :sales-id="selectedOrder"
      :format="chosenFormat"
      @on-close="showJobTicket = false"
    />
  </Teleport>
  <table
    class="quotation-table"
    :class="{ '!bg-white dark:!bg-gray-700': props.activeView === 'list' }"
  >
    <thead class="sticky top-[96px] z-10 border-l-4 shadow-sm backdrop-blur-md">
      <tr>
        <th v-if="props.activeView === 'list'" class="w-16 text-center">
          <UIButton
            :icon="['fad', 'angle-double-down']"
            class="!bg-transparent hover:!bg-theme-100"
            @click="toggleAllRows"
          />
        </th>
        <th
          v-if="visibleColumns['nr']"
          class="cursor-pointer text-theme-500 hover:text-theme-600"
          @click="sortBy('id', sortSettings.direction === 'desc' ? 'asc' : 'desc')"
        >
          {{ $t("nr.") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'id'"
            :icon="['fad', sortSettings.direction === 'desc' ? 'arrow-down-z-a' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </th>
        <th v-if="visibleColumns['ext']" class="w-16">{{ $t("ext.") }}</th>
        <th
          v-if="visibleColumns['type']"
          class="w-16 cursor-pointer text-theme-500 hover:text-theme-600"
          @click="sortBy('type', sortSettings.direction === 'desc' ? 'asc' : 'desc')"
        >
          {{ $t("type") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'type'"
            :icon="['fad', sortSettings.direction === 'desc' ? 'arrow-down-z-a' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </th>
        <th
          v-if="visibleColumns['date']"
          :class="{ 'w-24': visibleColumnsCount <= 6, 'w-20': visibleColumnsCount > 6 }"
          class="cursor-pointer text-theme-500 hover:text-theme-600"
          @click="sortBy('created_at', sortSettings.direction === 'desc' ? 'asc' : 'desc')"
        >
          {{ $t("date") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'created_at'"
            :icon="['fad', sortSettings.direction === 'desc' ? 'arrow-down-z-a' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </th>
        <th
          v-if="visibleColumns['customer']"
          class="cursor-pointer text-theme-500 hover:text-theme-600"
          @click="sortBy('user_id', sortSettings.direction === 'desc' ? 'asc' : 'desc')"
        >
          {{ $t("customer") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'user_id'"
            :icon="['fad', sortSettings.direction === 'desc' ? 'arrow-down-z-a' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </th>
        <th v-if="visibleColumns['company']">
          {{ $t("company") }}
        </th>
        <th v-if="visibleColumns['payment']">{{ $t("payment") }}</th>
        <th
          v-if="visibleColumns['status']"
          class="max-w-36 cursor-pointer text-theme-500 hover:text-theme-600"
          @click="sortBy('st', sortSettings.direction === 'desc' ? 'asc' : 'desc')"
        >
          {{ $t("status") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'st'"
            :icon="['fad', sortSettings.direction === 'desc' ? 'arrow-down-z-a' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </th>
        <th class="w-12 xl:w-auto">
          <div class="flex items-center justify-end gap-1">
            <span class="hidden xl:inline-block">{{ $t("actions") }}</span>
            <VDropdown>
              <UIButton
                :icon="['fal', 'sliders']"
                variant="neutral-light"
                class="ml-1"
                :disabled="saving"
              />
              <template #popper>
                <ol
                  class="divide-y divide-black rounded-md bg-gray-900 p-4 text-sm text-white shadow-md shadow-gray-200 dark:shadow-gray-900"
                >
                  <li
                    v-for="(value, column) in visibleColumns"
                    :key="column"
                    class="flex items-center"
                  >
                    <input
                      type="checkbox"
                      :checked="value"
                      :name="`displayField_${column}`"
                      @change="visibleColumns[column] = !visibleColumns[column]"
                    />
                    <label :for="`displayField_${column}`" class="ml-2">
                      {{ column }}
                    </label>
                  </li>
                </ol>
              </template>
            </VDropdown>
          </div>
        </th>
      </tr>
    </thead>

    <transition-group :name="`${props.activeView !== 'list' ? 'sales-list' : ''}`">
      <tbody
        v-for="quotation in props.quotations"
        :key="quotation.id"
        :class="[
          `border-l-4 border-${getStatusColor(quotation.status.code)}-300`,
          getHoverClass(quotation.id),
        ]"
        @mouseover="emit('update:hoveredQuotation', quotation.id)"
        @mouseleave="emit('update:hoveredQuotation', null)"
        @click="handleQuotationClick(quotation.id, quotation.items.length)"
      >
        <tr class="cursor-pointer">
          <td v-if="props.activeView === 'list'" class="text-center">
            <template v-if="quotation.items.length">
              <UIButton
                v-if="expandedRows[quotation.id]"
                :icon="['far', 'angle-up']"
                class="!bg-transparent hover:!bg-theme-100"
                @click.stop="quotation.items.length && toggleRow(quotation.id)"
              />
              <UIButton
                v-if="!expandedRows[quotation.id]"
                :icon="['far', 'angle-down']"
                class="!bg-transparent hover:!bg-theme-100"
                @click.stop="quotation.items.length && toggleRow(quotation.id)"
              />
            </template>
          </td>
          <td
            v-if="visibleColumns['nr']"
            v-tooltip="`#${quotation.order_nr}`"
            class="font-mono font-bold text-gray-400"
          >
            #{{ quotation.order_nr }}
          </td>
          <td v-if="visibleColumns['ext']">#{{ quotation.external_id }}</td>
          <td v-if="visibleColumns['type']" class="align-center">
            <SalesCreatedFromIndicator :created-from="quotation.created_from" with-next-status />
          </td>
          <td v-if="visibleColumns['date']" class="text-xs">
            {{ formatDateString(quotation.created_at) }}
          </td>
          <td v-if="visibleColumns['customer']">
            {{ fullName(quotation.customer) }}
          </td>
          <td
            v-if="visibleColumns['company']"
            v-tooltip="quotation.delivery_address?.company_name ?? ''"
          >
            {{ quotation.delivery_address?.company_name ?? "-" }}
          </td>
          <td v-if="visibleColumns['payment']">
            <small
              :class="[
                {
                  'text-green-500': quotation.payment_status === '1',
                  'text-orange-500': quotation.payment_status === 'partially paid',
                  'text-red-500': quotation.payment_status !== '1',
                },
              ]"
            >
              {{ paymentStatus(quotation.payment_status) }}
            </small>
          </td>
          <td v-if="visibleColumns['status']">
            <SalesStatusIndicator
              :status="quotation.status.code"
              :item-statuses="quotation.items.map((item) => item.status.code)"
              class="max-w-36"
            />
          </td>
          <td class="!overflow-visible">
            <div class="flex items-center justify-end gap-1">
              <UIButton
                class="hidden border border-theme-500 !bg-transparent capitalize xl:inline-block"
                :class="{ 'hover:!bg-theme-100 dark:hover:!bg-theme-900': !saving }"
                :disabled="saving"
                @click.stop="navigateTo(`/orders/${quotation.id}`)"
              >
                {{ $t("open") }}
              </UIButton>
              <ItemMenu
                v-if="!quotation.archived"
                menu-icon="ellipsis-h"
                menu-class="w-8 h-8 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600"
                dropdown-class="right-8 w-48 text-sm"
                :disabled="saving"
                :menu-items="menuItems"
                @item-clicked="menuItemClicked($event, quotation)"
              />
            </div>
          </td>
        </tr>
        <template v-if="props.activeView === 'grid'">
          <tr v-for="item in quotation.items.slice(1)" :key="`quotation_${item.id}`">
            <td :colspan="visibleColumnsCount + 1" />
          </tr>
        </template>
        <transition name="slide">
          <template v-if="props.activeView === 'list' && expandedRows[quotation.id]">
            <tr class="!h-16 !overflow-visible bg-gray-100 shadow-inner dark:bg-gray-800">
              <td />
              <td :colspan="visibleColumnsCount + 1" class="!overflow-visible">
                <OrderRows
                  v-for="item in quotation.items"
                  :key="item.id"
                  detailfields
                  :item="item"
                  :order_id="quotation.id"
                  :order="quotation"
                  :view="activeView"
                  ordertype="quotation"
                  class="flex w-full flex-shrink-0 items-center justify-between py-2 dark:border-gray-900"
                  @on-cancel-item="
                    emit('on-cancel-item', { quotationId: quotation.id, itemId: item.id })
                  "
                />
              </td>
            </tr>
          </template>
        </transition>
      </tbody>
    </transition-group>
  </table>
</template>

<script setup>
const { formatDateString } = useUtilities();

const { saving } = storeToRefs(useSalesStore());
const { getStatusColor } = useOrderStatus();
const { t: $t } = useI18n();

const props = defineProps({
  activeView: {
    type: String,
    required: true,
  },
  hoveredQuotation: {
    type: [Number, null],
    required: true,
  },
  quotations: {
    type: Array,
    required: true,
  },
  sortData: {
    type: Object,
    default: () => ({
      field: "created_at",
      direction: "desc",
    }),
  },
});

const emit = defineEmits([
  "update:hoveredQuotation",
  "on-archive-order",
  "on-cancel-item",
  "update:sort",
]);

const chosenFormat = ref(null);
const selectedOrder = ref(null);
const showJobTicket = ref(false);

const defaultVisibleColumns = {
  nr: true,
  ext: false,
  type: false,
  date: true,
  customer: true,
  company: true,
  payment: false,
  status: true,
};
const visibleColumns = ref(
  JSON.parse(localStorage.getItem("visibleOrdersColumns")) || defaultVisibleColumns,
);
const visibleColumnsCount = computed(() => {
  return Object.values(visibleColumns.value).filter(Boolean).length;
});
watch(
  visibleColumns,
  (newVal) => {
    localStorage.setItem("visibleOrdersColumns", JSON.stringify(newVal));
  },
  { deep: true },
);

const paymentStatus = (status) => {
  switch (status) {
    case "0":
      return $t("unpaid");
    case "partially paid":
      return $t("partially paid");
    case "1":
      return $t("paid");
    default:
      return $t("unpaid");
  }
};

const expandedRows = ref({});
const toggleRow = (quotationId) => {
  if (props.activeView !== "list") return;
  expandedRows.value[quotationId] = !expandedRows.value[quotationId];
};

const menuItems = [
  {
    heading: $t("Order actions"),
    items: [
      {
        action: "archive",
        icon: "trash-can",
        title: $t("archive order"),
        classes: "text-red-500",
        show: true,
      },
      {
        action: "show_job_ticket_XML",
        icon: "ticket",
        title: $t("jobticket XML"),
        show: true,
      },
      {
        action: "show_job_ticket_HTML",
        icon: "ticket",
        title: $t("jobticket HTML"),
        show: true,
      },
      {
        action: "show_job_ticket_PDF",
        icon: "ticket",
        title: $t("jobticket PDF"),
        show: true,
      },
    ],
  },
];

const sortSettings = ref(props.sortData);
function sortBy(field, direction) {
  sortSettings.value = {
    field,
    direction,
  };
  emit("update:sort", {
    field: field,
    direction: direction,
  });
}

function toggleAllRows() {
  if (props.activeView === "list") {
    if (Object.keys(expandedRows.value).length === props.quotations.length) {
      expandedRows.value = {};
    } else {
      expandedRows.value = props.quotations.reduce((acc, quotation) => {
        acc[quotation.id] = true;
        return acc;
      }, {});
    }
  }
}

function handleQuotationClick(quotationId, itemsLength) {
  if (props.activeView === "list") {
    return itemsLength && toggleRow(quotationId);
  } else {
    if (saving.value) return;
    return navigateTo(`/orders/${quotationId}`);
  }
}

function fullName(customer) {
  if (!customer || !customer.profile) return "";

  // Check if we have a complete name
  if (customer.profile.first_name && customer.profile.last_name) {
    const salutation = customer.profile.salutation ? `${customer.profile.salutation} ` : "";
    return salutation + `${customer.profile.first_name} ${customer.profile.last_name}`;
  }

  // Fallback to email
  return customer.email || "";
}

function menuItemClicked(event, quotation) {
  switch (event) {
    case "archive":
      emit("on-archive-order", quotation.id);
      break;
    case "show_job_ticket_HTML":
      selectedOrder.value = quotation.id;
      chosenFormat.value = "html";
      showJobTicket.value = true;
      break;
    case "show_job_ticket_XML":
      selectedOrder.value = quotation.id;
      chosenFormat.value = "xml";
      showJobTicket.value = true;
      break;
    case "show_job_ticket_PDF":
      selectedOrder.value = quotation.id;
      chosenFormat.value = "pdf";
      showJobTicket.value = true;
      break;
    default:
      break;
  }
}

const getHoverClass = (quotationId) => {
  if (saving.value) return "";
  if (props.activeView === "grid") {
    return {
      "!bg-gray-100 dark:!bg-gray-800 dark:!bg-gray-900 cursor-pointer":
        props.hoveredQuotation === quotationId,
    };
  } else {
    return { "hover:bg-gray-50 dark:hover:!bg-gray-800": true };
  }
};
</script>

<style lang="scss" scoped>
.quotation-table {
  @apply w-full table-fixed border-r bg-gray-50 text-left text-sm dark:border-gray-950 dark:bg-gray-800;

  tbody {
    @apply border-b border-b-gray-200 dark:border-b-black dark:hover:!bg-gray-900;
  }

  thead {
    @apply h-8 border-b-2 bg-gray-100/60 text-xs uppercase dark:border-black dark:bg-gray-800/60 dark:text-white !important;
  }

  tbody {
    tr {
      @apply h-12;
    }
  }

  td,
  th {
    @apply overflow-y-visible truncate px-2 py-1;
  }

  td:last-child,
  th:last-child {
    @apply text-right;
  }
}
</style>
