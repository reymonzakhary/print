<template>
  <SlideInModal
    :icon="['fas', 'file-invoice']"
    :title="$t('Review Invoice Details')"
    :show="props.show"
    @on-close="emit('on-close', $event)"
    @on-backdrop-click="emit('on-backdrop-click', $event)"
  >
    <InvoiceModalEmail
      v-if="showInvoiceModalEmail"
      :z-index="10000"
      :invoice-number="invoiceDetails.invoice_nr"
      @on-send="showInvoiceModalEmail = false"
      @on-close="showInvoiceModalEmail = false"
    />
    <PreviewLoader v-if="previewLoader" class="z-[10000]" />
    <PDFViewer v-if="showPDFViewer" :url="pdfURL" @on-close="showPDFViewer = false" />
    <div
      class="flex h-full flex-col text-sm dark:text-white md:w-[75vw] lg:w-[66vw] xl:w-[55vw] 2xl:w-[44vw]"
    >
      <template v-if="permissions.includes('transactions-read')">
        <div v-if="loading" class="grid flex-1 place-items-center">
          <div>
            <UILoader />
            <p class="mt-3 max-w-36 text-center italic text-theme-500 dark:text-theme-300">
              {{ $t("Generating preview of the invoice") }}
            </p>
          </div>
        </div>
        <div v-else-if="invoiceDetails" class="overflow-x-auto pb-3">
          <header class="relative flex items-center justify-between px-6 pb-3 pt-6">
            <h1 class="text-xl font-semibold">
              {{ $t("Invoice") }} #{{ invoiceDetails.invoice_nr }}
            </h1>
            <p class="absolute left-1/2 -translate-x-1/2 text-sm text-gray-500 dark:text-gray-300">
              {{ $t("Created on {dateString}", { dateString: invoiceDetails.created_at }) }}
            </p>
            <h2 v-if="invoiceDetails.custom_field?.order_nr" class="text-lg font-semibold">
              {{ $t("Order") }} #{{ invoiceDetails.custom_field?.order_nr }}
            </h2>
            <h2 v-else class="text-lg font-semibold">
              {{ $t("Order") }} #{{ invoiceDetails.order_id }}
            </h2>
          </header>
          <div class="grid grid-cols-3 gap-6 px-6 py-3">
            <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
              <h2 class="mb-2 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                {{ $t("Payment Status") }}
              </h2>
              <SalesStatusIndicator :status="invoiceDetails.st?.code ?? invoiceDetails.st" />
            </div>
            <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
              <h2 class="mb-2 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                {{ $t("Payment Method") }}
              </h2>
              <p>
                {{ $t("Invoice") }}
              </p>
            </div>
            <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
              <h2 class="mb-2 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                {{ $t("Due Date") }}
              </h2>
              <p>{{ invoiceDetails.due_date }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6 px-6 py-3">
            <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
              <h2 class="mb-2 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                {{ $t("Customer") }}
              </h2>
              <p class="font-semibold">{{ invoiceDetails.custom_field?.customer?.full_name }}</p>
              <p>
                {{ invoiceDetails.custom_field?.customer?.address_data?.address }}
                {{ invoiceDetails.custom_field?.customer?.address_data?.number }}
              </p>
              <p>
                {{ invoiceDetails.custom_field?.customer?.address_data?.zip_code }}
                {{ invoiceDetails.custom_field?.customer?.address_data?.city }}
              </p>
              <p>
                {{ invoiceDetails.custom_field?.customer?.address_data?.country }}
              </p>
              <br />
              <p v-if="invoiceDetails.custom_field?.customer?.vat">
                {{ $t("VAT-ID") }}: {{ invoiceDetails.custom_field?.customer?.vat }}
              </p>
            </div>
            <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
              <h2 class="mb-2 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                {{ $t("Producer") }}
              </h2>
              <p class="font-semibold">{{ invoiceDetails.custom_field?.supplier?.name }}</p>
              <p>
                {{ invoiceDetails.custom_field?.supplier?.address_data?.address }}
                {{ invoiceDetails.custom_field?.supplier?.address_data?.number }}
              </p>
              <p>
                {{ invoiceDetails.custom_field?.supplier?.address_data?.zip_code }}
                {{ invoiceDetails.custom_field?.supplier?.address_data?.city }}
              </p>
              <p>
                {{ invoiceDetails.custom_field?.supplier?.address_data?.country }}
              </p>
              <br />
              <p>
                <font-awesome-icon
                  v-if="!invoiceDetails.custom_field?.supplier?.coc"
                  class="text-orange-500"
                  :icon="['fal', 'triangle-exclamation']"
                />
                {{ $t("COC") }}:
                {{
                  !invoiceDetails.custom_field?.supplier?.coc
                    ? $t("Unknown")
                    : invoiceDetails.custom_field?.supplier?.coc
                }}
              </p>
              <p>
                <font-awesome-icon
                  v-if="!invoiceDetails.custom_field?.supplier?.tax_nr"
                  class="text-orange-500"
                  :icon="['fal', 'triangle-exclamation']"
                />
                {{ $t("VAT") }}:
                {{
                  !invoiceDetails.custom_field?.supplier?.tax_nr
                    ? $t("Unknown")
                    : invoiceDetails.custom_field?.supplier?.tax_nr
                }}
              </p>
            </div>
          </div>
          <div class="relative w-full overflow-x-auto px-6">
            <table class="my-3 w-full overflow-x-auto text-right text-gray-800 dark:text-gray-100">
              <thead>
                <tr
                  class="rounded bg-gray-100 text-right text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-100"
                >
                  <th scope="col" class="w-full min-w-40 px-4 py-3 text-left">
                    {{ $t("Product") }}
                  </th>
                  <th scope="col" class="px-4 py-3">{{ $t("Quantity") }}</th>
                  <th scope="col" class="whitespace-nowrap px-4 py-3">{{ $t("Unit Price") }}</th>
                  <th scope="col" class="px-4 py-3">{{ $t("Shipping") }}</th>
                  <th scope="col" class="px-4 py-3 text-left">{{ $t("VAT") }}</th>
                  <th scope="col" class="px-4 py-3">{{ $t("Total") }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="product in invoiceDetails.custom_field?.products"
                  :key="product.id"
                  class="border-gray-100 text-right dark:border-gray-900 [&:not(:last-child)]:border-b"
                >
                  <th
                    scope="row"
                    class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100"
                  >
                    {{ product.name }}
                  </th>
                  <td class="whitespace-nowrap px-4 py-3">{{ product.quantity }}</td>
                  <td class="whitespace-nowrap px-4 py-3">{{ product.display_unit_price }}</td>
                  <td class="whitespace-nowrap px-4 py-3">{{ product.display_shipping_cost }}</td>
                  <td class="whitespace-nowrap px-4 py-3 text-left">{{ product.vat }} %</td>
                  <td class="whitespace-nowrap px-4 py-3">{{ product.display_subtotal }}</td>
                </tr>
                <tr
                  v-for="service in invoiceDetails?.custom_field?.services"
                  :key="service.id"
                  class="border-gray-100 text-right dark:border-gray-900 [&:not(:last-child)]:border-b"
                >
                  <th scope="row" class="px-4 py-3 text-left">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ service.name }}</p>
                    <p class="text-xs font-normal text-gray-600 dark:text-gray-400">
                      {{ service.description }}
                    </p>
                  </th>
                  <td class="whitespace-nowrap px-4 py-3 text-right">{{ service.quantity }}</td>
                  <td class="whitespace-nowrap px-4 py-3">{{ service.display_unit_price }}</td>
                  <td class="whitespace-nowrap px-4 py-3">{{ service.display_shipping_cost }}</td>
                  <td class="whitespace-nowrap px-4 py-3 text-left">{{ service.vat }} %</td>
                  <td class="whitespace-nowrap px-4 py-3">
                    {{ service.display_total_incl_shipping_cost }}
                  </td>
                </tr>
              </tbody>
              <tbody>
                <tr
                  class="rounded bg-gray-100 text-right font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                  <td colspan="2" class="px-4 py-4 text-left text-xs uppercase">
                    {{ $t("Subtotal") }}
                  </td>
                  <td colspan="4" class="px-4 py-3">
                    {{ invoiceDetails.custom_field.display_subtotal }}
                  </td>
                </tr>
                <tr
                  class="rounded bg-gray-100 text-right font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                  <td colspan="2" class="px-4 py-4 text-left text-xs uppercase">
                    {{ $t("Shipping") }}
                  </td>
                  <td colspan="4" class="px-4 py-3">
                    {{ invoiceDetails.custom_field.display_total_shipping_cost }}
                  </td>
                </tr>
                <tr
                  class="rounded bg-gray-100 text-right font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                  <td colspan="2" class="px-4 py-4 text-left text-xs uppercase">
                    {{ $t("Total excl. VAT") }}
                  </td>
                  <td colspan="4" class="px-4 py-3">
                    {{ invoiceDetails.custom_field.display_total_ex }}
                  </td>
                </tr>
                <tr
                  v-for="vat in invoiceDetails.custom_field.vats"
                  :key="vat"
                  class="border-gray-100 text-right dark:border-gray-900 [&:not(:last-child)]:border-b"
                >
                  <td colspan="2" class="px-4 py-3 text-left">
                    {{ $t("vat") }} {{ vat.vat_percentage }} %
                  </td>
                  <td colspan="4" class="whitespace-nowrap px-4 py-3 text-right">
                    {{ vat.vat_display }}
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr
                  class="rounded bg-gray-100 text-right text-base font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-100"
                >
                  <td colspan="2" class="px-4 py-3 text-left font-bold uppercase">
                    {{ $t("Total") }}
                  </td>
                  <td colspan="4" class="whitespace-nowrap px-4 py-3 text-right">
                    {{ invoiceDetails.display_price }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <footer class="mt-auto">
          <div
            class="bg-gray-100 px-10 py-4 italic text-gray-800 dark:bg-gray-700 dark:text-gray-200"
          >
            {{
              // eslint-disable-next-line prettier/prettier
            $t("This is a draft invoice for review purposes only. Please click on the 'Send to Customer' button to send an official invoice to the customer. Or click 'Download PDF' to view or download the official invoice as a PDF.")
            }}
          </div>
          <div class="box-shadow px-10 py-4">
            <div class="flex">
              <button
                v-tooltip="
                  // eslint-disable-next-line prettier/prettier
                $t('We are still building this! Please download the PDF and send it manually, for now.')
                "
                class="w-full rounded-l-full bg-theme-500 px-2 py-1 text-sm text-themecontrast-900 transition-colors duration-150 hover:bg-theme-600 disabled:opacity-50 disabled:hover:bg-theme-500"
                disabled
                @click="handleSendToCustomer"
              >
                <font-awesome-icon :icon="['fal', 'paper-plane']" class="mr-1" />
                {{ $t("Send to Customer") }}
              </button>
              <button
                class="w-full rounded-r-full border border-theme-500 bg-transparent px-2 py-1 text-sm text-theme-500 transition-colors duration-150 hover:bg-theme-100"
                @click="handleDownloadPDF"
              >
                {{ $t("Download PDF") }}
                <font-awesome-icon :icon="['fal', 'file-download']" class="ml-1" />
              </button>
            </div>
          </div>
        </footer>
      </template>
      <div v-else class="grid flex-1 place-items-center">
        <NoPermissions :message="$t('You do not have permissions to view this invoice.')" />
      </div>
    </div>
  </SlideInModal>
</template>

<script setup>
// import { format } from "date-fns";

import UISelector from "~/components/global/ui/UISelector.vue";

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  orderId: {
    type: Number,
    required: true,
  },
  orderNumber: {
    type: [String, undefined],
    required: false,
    default: undefined,
  },
  invoice: {
    type: [Object, null],
    required: false,
    default: null,
  },
});

const emit = defineEmits(["on-close", "on-backdrop-click"]);

const { t: $t } = useI18n();
const { addToast } = useToastStore();
const { permissions } = storeToRefs(useAuthStore());
const { handleError } = useMessageHandler();
const invoiceRepository = useInvoiceRepository();

const changed = ref(false);
const loading = ref(false);
const invoiceDetails = ref(null);
const showInvoiceModalEmail = ref(false);

// const formattedDueDate = computed(() => {
//   if (invoiceDetails.value && !isNaN(Date.parse(invoiceDetails.value.dueDate))) {
//     return format(new Date(invoiceDetails.value.dueDate), "dd-MM-yyyy HH:mm");
//   }
//   return "";
// });

async function fetchInvoice() {
  try {
    loading.value = true;
    const invoices = await invoiceRepository.getAllInvoices(props.orderId, props.orderNumber);
    invoiceDetails.value = invoices.reduce(
      (max, invoice) => (invoice.id > max.id ? invoice : max),
      invoices[0],
    );
  } catch (err) {
    handleError(err);
  } finally {
    loading.value = false;
  }
}

async function handleSaveInvoice() {
  try {
    await invoiceRepository.saveInvoice(invoiceDetails.value);
    addToast({
      message: $t("Invoice saved successfully."),
      type: "success",
    });
    changed.value = false;
  } catch (err) {
    handleError(err);
  }
}

function handleSendToCustomer() {
  showInvoiceModalEmail.value = true;
}

const previewLoader = ref(false);
const showPDFViewer = ref(false);
const pdfURL = ref(null);
async function handleDownloadPDF() {
  try {
    previewLoader.value = true;
    const url = await invoiceRepository.downloadInvoicePDF(props.orderId, invoiceDetails.value.id);
    pdfURL.value = url;
    showPDFViewer.value = true;
  } catch (error) {
    handleError(error);
  } finally {
    previewLoader.value = false;
  }
}

watch(
  () => props.show,
  (newValue) => {
    if (newValue && props.orderId && !props.invoice) {
      fetchInvoice();
    }
    if (newValue && props.invoice) {
      invoiceDetails.value = props.invoice;
    }
  },
  { immediate: true },
);
</script>
