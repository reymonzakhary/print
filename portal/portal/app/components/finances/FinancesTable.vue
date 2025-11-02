<template>
  <div class="finances-table-container">
    <UICard :class="{ 'rounded-b-md': pagination.last_page === 1 }">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('Invoices')" :icon="['fal', 'file-invoice-dollar']" />
        </template>
        <template #right>
          <UIInputText
            v-model="searchInput"
            name="search"
            type="text"
            :placeholder="$t('Search invoices...')"
            :prefix="['fal', 'search']"
            class="w-64"
            @input="handleSearch"
          />
        </template>
      </UICardHeader>
      <div v-if="loading" class="flex justify-center py-8">
        <font-awesome-icon
          :icon="['fal', 'spinner-third']"
          class="animate-spin text-theme-500"
          size="2x"
        />
      </div>
      <div v-else class="w-full">
        <!-- Header -->
        <div
          class="flex w-full border-b bg-white/60 text-left text-sm font-bold uppercase backdrop-blur-md dark:border-gray-900 dark:bg-gray-800/40"
        >
          <div class="w-1/12 px-4 py-2">{{ $t("Invoice Nr") }}</div>
          <div class="w-1/12 px-4 py-2">{{ $t("Type") }}</div>
          <div class="w-1/12 px-4 py-2">{{ $t("Order Nr") }}</div>
          <div class="w-2/12 px-4 py-2">{{ $t("Customer") }}</div>
          <div class="w-1/12 px-4 py-2">{{ $t("Date") }}</div>
          <div class="w-2/12 px-4 py-2">{{ $t("Status") }}</div>
          <div class="w-2/12 px-4 py-2">{{ $t("Total") }}</div>
          <div class="w-1/12 px-4 py-2">{{ $t("Due Date") }}</div>
          <div class="w-1/12 px-4 py-2">{{ $t("Actions") }}</div>
        </div>

        <!-- Rows -->
        <div
          v-for="(row, index) in data"
          :key="`invoice-${index}`"
          class="flex w-full cursor-pointer items-center border-b hover:bg-gray-50 dark:border-gray-900 dark:hover:bg-gray-800"
          :class="{ 'last-of-type:rounded-b-md': pagination.last_page === 1 }"
          @click="$emit('row-click', row)"
        >
          <div class="w-1/12 px-4 py-2 text-sm font-bold text-gray-400">#{{ row.invoice_nr }}</div>
          <div class="w-1/12 px-4 py-2">
            <!-- {{ row.type }} -->
            <font-awesome-icon
              v-tooltip="$t('this is a {type} invoice', { type: row.type })"
              :icon="getTypeIcon(row.type)"
              class="text-theme-500"
              fixed-width
            />
          </div>
          <div class="w-1/12 px-4 py-2 text-sm font-medium text-theme-500">
            <NuxtLink :to="`/orders/${row.order_id}`"
              >#{{ row.custom_field.order_nr ?? row.order_id }}</NuxtLink
            >
          </div>
          <div class="w-2/12 px-4 py-2 text-sm">
            <strong>{{ row.custom_field?.customer?.company_name }}</strong>
            <span
              v-if="
                row.custom_field?.customer?.company_name && row.custom_field?.customer?.full_name
              "
            >
              -
            </span>
            {{ row.custom_field?.customer?.full_name }}
          </div>
          <div class="w-1/12 px-4 py-2 text-sm">
            <time :datetime="row.created_at">
              {{ new Date(row.created_at).toLocaleDateString() }}
            </time>
          </div>
          <div class="w-2/12 px-4 py-2">
            <SalesStatusIndicator :status="row.st.code" />
          </div>
          <div class="w-2/12 px-4 py-2">
            <pre>{{ row.custom_field.display_incl_vat }}</pre>
          </div>
          <div class="w-1/12 px-4 py-2 text-sm">
            <time :datetime="row.due_date">
              {{
                new Date(row.due_date).toLocaleDateString("en-GB", {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                })
              }}
            </time>
          </div>
          <div class="w-1/12 px-4 py-2">
            <div class="flex space-x-2">
              <UIButton size="xs" :icon="['fal', 'download']" @click.stop="downloadInvoice(row)" />
              <UIButton size="xs" :icon="['fal', 'eye']" @click.stop="$emit('row-click', row)" />
            </div>
          </div>
        </div>
      </div>
      <div
        v-if="pagination && pagination.last_page > 1 && !loading"
        class="flex items-center justify-between border-t px-4 dark:border-gray-900"
      >
        <LocalPagination
          v-if="pagination.last_page > 1"
          :pagination="pagination"
          class="my-2"
          @pagination="$emit('page-change', $event.page)"
        />
      </div>
    </UICard>
  </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { debounce } from "lodash";
import { useInvoiceRepository } from "~/repositories/useInvoiceRepository";

const { t: $t } = useI18n();
const invoiceRepository = useInvoiceRepository();

const props = defineProps({
  // Table specific props
  data: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  getInvoices: {
    type: Function,
    required: true,
  },
  hover: {
    type: Boolean,
    default: false,
  },
  pagination: {
    type: Object,
    default: null,
  },
  filter: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["row-click", "page-change"]);

const searchInput = ref(props.filter || "");

const handleSearch = debounce(async () => {
  try {
    const currentPageSize = props.pagination?.per_page || 20;
    await props.getInvoices(currentPageSize, 1, searchInput.value);
  } catch (error) {
    console.error("Search failed:", error);
    // Consider showing user-friendly error message
  }
}, 300);

watch(
  () => props.filter,
  (newVal) => {
    searchInput.value = newVal;
  },
);

function getTypeIcon(type) {
  const typeLower = type?.toLowerCase() || "";
  if (typeLower === "single") {
    return ["fal", "file-invoice"];
  } else if (typeLower === "splitted") {
    return ["fal", "send-backward"];
  } else if (typeLower === "credit note") {
    return ["fal", "money-bill-simple-wave"];
  }
  return ["fal", "file-invoice"];
}

async function downloadInvoice(row) {
  try {
    await invoiceRepository.downloadTransactionInvoicePDF(row.order_id, row);
  } catch (error) {
    console.error("Error downloading invoice:", error);
  }
}
</script>
