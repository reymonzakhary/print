<template>
  <NotFound v-if="!isInitializing && !quotation" />
  <main v-else-if="!error" class="h-full print:overflow-visible">
    <StudioEmailModal
      v-if="showEmailModal"
      type="quotation"
      :entity-id="id"
      @on-send="handleSendEmail"
      @on-close="showEmailModal = false"
      @on-save="handleSaveEmailDraft"
    />
    <div class="grid h-full grid-rows-[64px_,_1fr] overflow-hidden print:!overflow-visible">
      <SkeletonLine v-if="isInitializing" :height="8" class="m-4" />
      <QuotationPageHeader
        v-else
        :id="id"
        :external-id="quotation.external_id"
        class="p-4"
        :prev="quotationMeta.prev"
        :next="quotationMeta.next"
        :saving="saving"
        @on-close="
          navigateTo({
            path: '/quotations',
            query: {
              tab:
                criticalFlag === 'trashed'
                  ? 'bin'
                  : quotation.status.code == statusMap.DRAFT
                    ? 'drafts'
                    : '',
            },
          })
        "
      />
      <div class="hidden print:block" />
      <div
        class="grid h-full grid-cols-12 gap-4 overflow-y-auto rounded p-4 pt-0 print:!overflow-y-visible"
        :class="{
          'relative mx-[8px] h-[calc(100%_-_9px)]': criticalFlag,
          'outline-gray-300': criticalFlag === 'locked',
          'outline-red-300': criticalFlag === 'trashed',
        }"
        :style="outlineStyle"
      >
        <div
          v-if="quotation && criticalFlag"
          class="fixed left-[calc(50%_+_64px)] top-[116px] z-50 w-fit -translate-x-1/2 rounded-b px-8 pb-1 text-sm font-bold shadow-md"
          :class="{
            'bg-gray-300 text-gray-500': criticalFlag === 'locked',
            'bg-red-300 text-red-500': criticalFlag === 'trashed',
          }"
        >
          <template v-if="criticalFlag === 'locked'">
            {{ $t("locked by:") }} {{ quotation.locked_by.email }}
          </template>
          <template v-else-if="criticalFlag === 'trashed'">
            {{ $t("trashed") }}
          </template>
        </div>

        <!-- Details -->
        <div class="col-span-12 h-full sm:col-span-4 lg:col-span-3 2xl:col-span-2 print:col-span-3">
          <SkeletonLine v-if="isInitializing" class="h-full" />
          <QuotationDetails
            v-else
            :quotation="quotation"
            :sales-id="id"
            @on-delete-quotation="handleDeleteQuotation"
          />
        </div>

        <!-- Products -->
        <div
          class="col-span-12 flex flex-col overflow-visible sm:col-span-4 lg:col-span-6 2xl:col-span-8 print:col-span-9"
        >
          <template v-if="hasPermissionGroup(newPermissions.quotations.groups.moduleDetails)">
            <SkeletonLine v-if="isInitializing" class="h-full" />
            <QuotationProducts
              v-else
              :class="{
                'flex min-h-[calc(100vh_-_348px)] flex-col justify-center md:min-h-[calc(100vh_-_270px)] print:!overflow-visible':
                  !quotation.items.length,
              }"
              sales-type="quotation"
              :items="quotation.items"
              :sales-id="id"
              @on-product-added="handleProductAdded"
              @on-product-removed="handleProductRemoved"
              @on-product-updated="handleProductUpdated"
              @on-open-product-added="handleOpenProductAdded"
            />
          </template>
          <div
            v-else
            class="flex min-h-[calc(100vh_-_348px)] flex-col justify-center md:min-h-[calc(100vh_-_270px)]"
          >
            <NoPermissions :message="$t('You do not have permission to view products.')" />
          </div>
          <template
            v-if="
              permissions.includes('quotations-services-list') ||
              permissions.includes('quotations-discount-access')
            "
          >
            <hr class="mb-2 mt-6" />
            <SkeletonLine v-if="isInitializing" class="h-8" />
            <QuotationServices
              v-else-if="isEditable || quotation.services.length"
              :services="quotation.services"
              :quotation="quotation"
              :sales-id="id"
              @on-service-added="handleServiceAdded"
              @on-service-updated="handleServiceUpdated"
              @on-service-removed="handleServiceRemoved"
            />
          </template>
        </div>

        <!-- Price & Actions -->
        <div class="col-span-12 sm:col-span-4 lg:col-span-3 2xl:col-span-2 print:hidden">
          <SkeletonLine v-if="isInitializing" class="h-full" />
          <div
            v-else-if="quotation.items.length || quotation.services.length"
            class="sticky top-0 flex h-[calc(100vh_-_64px_-_85px)] flex-col gap-4"
          >
            <SalesPriceSummary
              v-if="hasPermissionGroup(newPermissions.quotations.groups.moduleDetails)"
              class="flex-1 overflow-y-auto"
              :items="quotation.items"
              :services="quotation.services"
              :subtotal="quotation.display_subtotal_price"
              :shipping-costs="quotation.display_shipping_cost"
              :vat="reducedVatArray"
              :total-vat="quotation.display_vat_price"
              :total="quotation.display_total_price"
            />
            <QuotationActions
              v-if="!isInitializing && permissions.includes('quotations-update')"
              class="print:hidden"
              :status="quotation.status.code"
              @on-finalize-quotation="() => changeStatus(statusMap.NEW)"
              @on-done-editing="handleToggleEdit(false)"
              @on-edit-quotation="handleToggleEdit(true)"
              @on-send-quotation="showEmailModal = true"
              @on-convert-to-order="handleConvertToOrder"
              @on-reject-quotation="() => changeStatus(statusMap.REJECTED)"
              @on-accept-quotation="handleAcceptQuotation"
              @on-dev-edit-status="(e) => changeStatus(e)"
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
    @reset-error="
      async () => {
        error = null;
        await navigateTo('/quotations');
      }
    "
    @clear-error="error = null"
  />
</template>

<script setup>
import { useStore } from "vuex";

const route = useRoute();
const id = route.params.id;
const trashed = route.query.trashed;

const { t: $t } = useI18n();

const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const { statusMap, isStatusInGroup } = useOrderStatus();
const { confirm } = useConfirmation();

const { permissions: newPermissions, hasPermissionGroup } = usePermissions();
const { theUser, permissions } = storeToRefs(useAuthStore());
const {
  changed,
  saving,
  criticalFlag,
  isEditable,
  isExternal,
  noMembersAccess,
  salesContext,
  pickupAddresses,
  leaving,
} = storeToRefs(useSalesStore());
const salesStore = useSalesStore();
const store = useStore();

const quotationRepository = useQuotationRepository();

const isInitializing = ref(true);
const showEmailModal = ref(false);

const quotation = ref(null);
const quotationMeta = ref(null);

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
  if (!permissions.value.includes("members-list")) noMembersAccess.value = true;
  await getQuotation();
});

onBeforeUnmount(() => {
  leaving.value = true;
  salesStore.reset();
});

function setWarnings() {
  if (!quotation.value.items.length && !quotation.value.services.length) {
    salesStore.addWarning("items", $t("please add products or services to this quotation"));
  } else {
    salesStore.removeWarning("items");
  }
}

const error = ref(null);
onErrorCaptured((err, instance, info) => {
  error.value = {
    statusCode: 500,
    message: err.message,
  };
  console.log("error: ", err, "instance: ", instance, "info: ", info);
  return false; // Prevent the error from propagating further
});

const abortController = ref(null);
async function getQuotation(withoutInit = false) {
  store.commit(`fm/content/emptyFiles`);
  if (abortController.value) {
    abortController.value.abort("Abort previous request");
  }
  abortController.value = new AbortController();

  if (!permissions.value.includes("quotations-read")) {
    addToast({
      type: "error",
      message: $t("You do not have permission to view quotations."),
    });
    navigateTo("/quotations");
    return;
  }
  try {
    if (!withoutInit) isInitializing.value = true;
    const [resData, resMeta] = await quotationRepository.getQuotationById(id, trashed, {
      signal: abortController.value.signal,
    });
    await nextTick();

    quotation.value = resData;
    quotationMeta.value = resMeta;

    salesContext.value = resData.context?.id ?? false;

    isExternal.value = resData.external_connection;

    if (resData.locked_by && resData.locked_by?.id !== theUser.value.id) {
      criticalFlag.value = "locked";
    } else if (trashed) {
      criticalFlag.value = "trashed";
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
      permissions.value.includes("quotations-update") &&
      criticalFlag.value !== "trashed" &&
      resData.status &&
      isStatusInGroup(resData.status.code, "EDITING") &&
      criticalFlag.value !== "locked";
  } catch (error) {
    if (error.message.includes("Abort previous request")) return;
    handleError(error);
  } finally {
    if (!withoutInit) isInitializing.value = false;
    setWarnings();
  }
}

async function handleDeleteQuotation() {
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
    addToast({
      type: "success",
      message: $t("quotation deleted"),
    });
    if (quotation.value.status?.code === statusMap.DRAFT) {
      navigateTo({ path: "/quotations", query: { type: "drafts" } });
    } else {
      navigateTo("/quotations");
    }
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleConvertToOrder() {
  try {
    saving.value = true;
    await confirm({
      title: $t("convert to order"),
      message: $t("are you sure you want to convert this quotation to an order?"),
      confirmOptions: {
        label: $t("convert"),
        variant: "success",
      },
    });
    await quotationRepository.updateQuotation(id, { type: "order" });
    addToast({
      type: "success",
      message: $t("quotation converted to order"),
    });
    navigateTo(`/orders/${id}`);
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
  getQuotation(true);
}

function handleProductUpdated() {
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

function handleProductRemoved(productId) {
  quotation.value.items = quotation.value.items.filter((item) => item.id !== productId);
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

function handleOpenProductAdded(newProduct) {
  quotation.value = { ...quotation.value, items: [...quotation.value.items, newProduct] };
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

function handleServiceAdded(newService) {
  quotation.value.services.push(newService);
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

function handleServiceUpdated(updatedService) {
  const serviceIndex = quotation.value.services.findIndex(
    (service) => service.id === updatedService.id,
  );
  quotation.value.services[serviceIndex] = updatedService;
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

function handleServiceRemoved(serviceId) {
  quotation.value.services = quotation.value.services.filter((service) => service.id !== serviceId);
  // Refetch quotation to get the correct prices
  getQuotation(true);
}

async function handleAcceptQuotation() {
  try {
    saving.value = true;
    const totalPrice = quotation.value.price;
    if (totalPrice === 0) {
      await confirm({
        title: $t("accept quotation"),
        message: $t("The total price of your products is 0. Are you sure you want to accept it?"),
        confirmOptions: {
          label: $t("accept"),
          variant: "success",
        },
      });
    }
    await quotationRepository.emailQuotation(id);
    // Refetch quotation to get the new status
    getQuotation(true);
  } catch (err) {
    if (err.cancelled) return;
    handleError(err);
  } finally {
    saving.value = false;
  }
}

async function changeStatus(status) {
  saving.value = true;
  try {
    await quotationRepository.updateQuotation(id, { st: status });
    await getQuotation(true);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleSendEmail(data) {
  try {
    saving.value = true;
    await quotationRepository.emailQuotation(id, data);
    addToast({
      type: "success",
      message: $t("quotation sent to customer"),
    });
    showEmailModal.value = false;
    // Refetch quotation to retrieve communication log and update status
    await getQuotation(true);
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}

function handleSaveEmailDraft(draftData) {
  // Handle saving the email draft
  // You can implement this based on your backend API for saving drafts
  addToast({
    type: "success",
    message: $t("Email draft saved successfully"),
  });
}

async function handleToggleEdit(editing) {
  const payload = {
    editing,
  };

  if (quotation.value.status.code === statusMap.WAITING_FOR_RESPONSE) {
    payload.st = statusMap.NEW;
  }

  if (quotation.value.status.is("MAILED")) {
    payload.st = statusMap.NEW;
  }

  if (quotation.value.status.code === statusMap.REJECTED) {
    payload.st = statusMap.NEW;
  }

  if (quotation.value.status.code === statusMap.ACCEPTED) {
    payload.st = statusMap.NEW;
  }

  try {
    saving.value = true;
    await quotationRepository.updateQuotation(id, payload);
    await getQuotation(true);
  } catch (err) {
    handleError(err);
  } finally {
    saving.value = false;
  }
}

watch(
  () => salesContext.value,
  async () => {
    if (salesContext.value) {
      if (!permissions.value.includes("contexts-list")) return;
      pickupAddresses.value = await quotationRepository.getPickupAddresses(salesContext.value);
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
      type: "info",
      message: $t("Please wait till we're done saving your changes."),
    });
    return next(false);
  }
  leaving.value = true;

  salesStore.reset();
  next();
});
</script>
