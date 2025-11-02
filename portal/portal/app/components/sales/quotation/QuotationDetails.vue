<template>
  <SkeletonLine v-if="isInitializing" class="h-full" />
  <div v-else class="flex h-full flex-col gap-4">
    <Teleport to="body">
      <SalesHistory
        v-if="showHistoryModal"
        :sales-id="props.salesId"
        sales-type="quotation"
        @on-close="showHistoryModal = false"
      />
      <AddressFormModal
        v-if="showAddressFormModal"
        :is-creating-for-team="isUsingTeamAddresses"
        :teams="teams"
        :team="chosenTeam"
        @close-modal="showAddressFormModal = false"
        @create-address="handleCreateAddress"
        @on-team-change="chosenTeam = $event"
      />
    </Teleport>

    <div class="sticky top-0 z-40 -mb-4 bg-gray-100 pb-4 dark:bg-gray-800">
      <PreviewLoader v-if="previewLoader" />
      <PDFViewer v-if="showPDFViewer" :url="pdfURL" @on-close="showPDFViewer = false" />
      <QuotationDetailsHeader
        :created-at="props.quotation.created_at"
        :status="props.quotation.status.code"
        class="mb-4"
        @on-print-quotation="handlePrintQuotation"
        @on-show-history="showHistoryModal = true"
        @on-delete-quotation="emit('on-delete-quotation', $event)"
      />
      <SalesStatusIndicator :status="props.quotation.status.code" />
    </div>
    <SalesUserSelector
      v-model="quotationDetails.customer"
      :only-show="isExternal || noMembersAccess"
      :users="chooseableUsers"
      @user-created="handleUserCreated"
    />
    <div class="flex flex-col gap-4">
      <SalesPaymentMethod v-model="quotationDetails.paymentMethod" />
      <div v-if="isEditable && !isExternal && !noMembersAccess" class="py-1">
        <div class="flex items-center gap-4 pb-2">
          <label for="multipleAddresses" class="flex-1 text-sm text-gray-500">
            <font-awesome-icon :icon="['fal', 'share-nodes']" class="fa-fw" />
            {{ $t("multiple addresses") }}
          </label>
          <UISwitch
            class="mt-[2px]"
            name="multipleAddresses"
            :disabled="!hasPermissionGroup(newPermissions.quotations.groups.itemAddressUpdate)"
            :value="multipleAddresses"
            @input="handleToggleMultipleAddresses($event)"
          />
        </div>
        <div
          v-if="!multipleAddresses"
          class="flex items-center gap-4 border-t border-gray-200 pt-2 dark:border-black"
        >
          <label for="differentInvoice" class="flex-1 text-sm text-gray-500">
            <font-awesome-icon :icon="['fal', 'share-nodes']" class="fa-fw" />
            {{ $t("different invoice address") }}
          </label>
          <UISwitch
            :disabled="
              quotationDetails.customerAddress?.id === null ||
              !hasPermissionGroup(newPermissions.quotations.groups.invoiceAddressUpdate)
            "
            class="mt-[2px]"
            name="differentInvoice"
            :value="quotationDetails.differentInvoice"
            @input="quotationDetails.differentInvoice = $event"
          />
        </div>
      </div>
      <SalesDeliverySelector
        v-if="!multipleAddresses && hasPermission('members-list')"
        :user="quotationDetails.customer"
        :team="chosenTeam"
        :method="quotationDetails.deliveryMethod"
        :address="quotationDetails.customerAddress"
        :only-show="
          isExternal ||
          noMembersAccess ||
          !hasPermissionGroup(newPermissions.quotations.groups.deliveryAddressUpdate)
        "
        :method-disabled="!hasPermissionGroup(newPermissions.quotations.groups.pickupAddressSelect)"
        optional
        @on-new-address="showAddressFormModal = 'delivery'"
        @update:team="chosenTeam = $event"
        @update:method="quotationDetails.deliveryMethod = $event"
        @update:address="quotationDetails.customerAddress = $event"
        @on-team-assigned="handleTeamAssigned"
      />
      <SalesInvoiceSelector
        v-if="
          (multipleAddresses ||
            quotationDetails.differentInvoice ||
            quotationDetails.customerAddress?.id === null) &&
          hasPermission('members-list')
        "
        v-model="quotationDetails.invoiceAddress"
        v-tooltip.right="
          !multipleAddresses & isUsingTeamAddresses && !chosenTeam ? $t('select a team first') : ''
        "
        :user="quotationDetails.customer"
        :team="chosenTeam"
        :addresses="chooseableAddresses"
        :disabled="!multipleAddresses && isUsingTeamAddresses && !chosenTeam"
        :only-show="
          isExternal ||
          noMembersAccess ||
          !hasPermissionGroup(newPermissions.quotations.groups.invoiceAddressUpdate)
        "
        @on-new-address="showAddressFormModal = 'invoice'"
        @update:team="chosenTeam = $event"
      />
      <SalesReferenceInput
        v-model="quotationDetails.reference"
        :disabled="!permissions.includes('quotations-reference-update')"
        @blur="handleInstantSave"
      />
      <SalesNoteInput
        v-model="quotationDetails.note"
        :disabled="!permissions.includes('quotations-note-update')"
        @blur="handleInstantSave"
      />
      <section v-if="permissions.includes('quotations-media-access')">
        <OrderFiles
          v-if="!isInitializing"
          class="rounded-lg border border-gray-200 p-4 shadow-sm dark:border-gray-700"
          type="quotations"
          order-type="quotation"
          :order_id="Number(salesId)"
          :index="Math.random() * 100"
          :editable="isEditable"
          :ext_connection="isExternal"
          :object="props.quotation"
          :prop-driven="true"
          :upload-not-allowed="!permissions.includes('quotations-media-create')"
          :delete-not-allowed="!permissions.includes('quotations-media-delete')"
          :list-not-allowed="!permissions.includes('quotations-media-list')"
        />
        <NuxtLink
          v-if="
            !isExternal &&
            permissions.includes('quotations-media-list') &&
            props.quotation?.attachments?.length > 0
          "
          :to="`/filemanager?path=quotations/${Number(salesId)}`"
          class="-mt-2 block w-full items-center rounded-b-md bg-gray-200 p-2 text-center text-sm text-theme-500 hover:text-theme-600 dark:bg-gray-700 dark:hover:text-theme-400"
        >
          <font-awesome-icon :icon="['fal', 'folder']" class="mr-1" />
          {{ $t("Open Media Manager") }}
          <font-awesome-icon :icon="['fal', 'external-link']" class="ml-1" />
        </NuxtLink>
      </section>
      <SalesCommunication :mails="quotationDetails.mails" />
    </div>
  </div>
</template>

<script setup>
import { isEqual } from "lodash";
const config = useRuntimeConfig();

const props = defineProps({
  quotation: {
    type: Object,
    required: true,
  },
  salesId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits(["on-quotation-updated", "on-delete-quotation"]);

const { t: $t } = useI18n();

const api = useAPI();
const { permissions: newPermissions, hasPermission, hasPermissionGroup } = usePermissions();
const { permissions } = storeToRefs(useAuthStore());
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

const quotationRepository = useQuotationRepository();
const privateRepository = usePrivateRepository();

const {
  isEditable,
  isExternal,
  noMembersAccess,
  saving,
  teams,
  isUsingTeamAddresses,
  multipleAddresses,
  deliveryAddresses,
  leaving,
} = storeToRefs(useSalesStore());
const { addWarning, removeWarning } = useSalesStore();

const isInitializing = ref(true);
const chooseableUsers = ref([]);
const chosenTeam = ref(null);

const showHistoryModal = ref(false);
const showAddressFormModal = ref(false);

const quotationDetails = ref({
  customer: {},
  paymentMethod: "invoice",
  deliveryMethod: "delivery",
  customerAddress: null,
  differentInvoice: false,
  invoiceAddress: null,
  reference: null,
  note: null,
  mails: [],
});
const watchedQuotationDetails = computed(() => JSON.parse(JSON.stringify(quotationDetails.value)));

const chooseableAddresses = computed(() => {
  return deliveryAddresses.value.filter((address) => {
    return !chosenTeam.value || `${address.team_id}` === `${chosenTeam.value}`;
  });
});

async function handleToggleMultipleAddresses(value) {
  multipleAddresses.value = value;
}

async function handleDetailsUpdated() {
  if (!JSON.parse(localStorage.getItem("quotationSaveInfoShown"))) {
    addToast({
      type: "success",
      message: $t("quotation saved!"),
    });
    addToast({
      type: "info",
      message: $t("your quotations are automatically saved when changes are made."),
    });
    localStorage.setItem("quotationSaveInfoShown", "true");
  }
}

async function handleTeamAssigned() {
  await updateChooseableUsers();
}

async function handleUserCreated(newUser) {
  const users = await fetchChooseableUsers();
  chooseableUsers.value = users;
  const user = users.find((user) => user.id === newUser.id);
  quotationDetails.value.customer = user;
  deliveryAddresses.value = user.addresses;
  teams.value = user.teams;
}

async function fetchChooseableUsers() {
  try {
    return await privateRepository.get();
  } catch (error) {
    handleError(error);
    if (error.status === 403) noMembersAccess.value = true;
    return false;
  }
}

async function updateChooseableUsers() {
  const users = await fetchChooseableUsers();
  chooseableUsers.value = users;
  if (props.quotation?.customer?.id) {
    const user = users.find((user) => user.id === props.quotation.customer.id);
    quotationDetails.value.customer = user;
    if (user?.addresses && !isEqual(deliveryAddresses.value, user.addresses)) {
      deliveryAddresses.value = user.addresses;
    }
    teams.value = user.teams;
  }
}

async function handleCreateAddress(address) {
  const newAddress = { ...address };

  if (address.full_name?.length === 0) {
    newAddress.full_name = quotationDetails.value.customer.name;
  }

  if (!address.is_business_user) {
    newAddress.company_name = "No Company";
    newAddress.tax_nr = "000000000";
    newAddress.phone_number = "000000000";
  } else if (address.company_name && address.company_name.length === 0) {
    newAddress.company_name = "No Company";
    newAddress.tax_nr = "000000000";
    newAddress.phone_number = "000000000";
  }

  const userAddressEndpoint = `${quotationDetails.value.customer.isUser ? "users" : "members"}/${quotationDetails.value.customer.id}/addresses`;
  const teamAddressEndpoint = `teams/${chosenTeam.value}/addresses`;

  const apiUrl = isUsingTeamAddresses.value ? teamAddressEndpoint : userAddressEndpoint;
  try {
    const { data } = await api.post(apiUrl, newAddress);

    await updateChooseableUsers();

    if (showAddressFormModal.value === "invoice") {
      quotationDetails.value.invoiceAddress = deliveryAddresses.value.find((a) => a.id === data.id);
    } else {
      quotationDetails.value.customerAddress = deliveryAddresses.value.find(
        (a) => a.id === data.id,
      );
    }

    addToast({
      type: "success",
      message: $t("Address created successfully."),
    });
    showAddressFormModal.value = false;
  } catch (error) {
    handleError(error);
  }
}

function setWarnings() {
  if (!quotationDetails.value.customer) {
    addWarning("customer", $t("please select a customer"));
  } else {
    removeWarning("customer");
  }
}

const previewLoader = ref(false);
const showPDFViewer = ref(false);
const pdfURL = ref(null);
async function handlePrintQuotation() {
  try {
    previewLoader.value = true;
    const response = await api.get(`/quotations/${props.salesId}/render/pdf`, {
      responseType: "arrayBuffer",
    });
    const url = window.URL.createObjectURL(new Blob([response], { type: "application/pdf" }));
    pdfURL.value = url;
    showPDFViewer.value = true;
  } catch (error) {
    handleError(error);
  } finally {
    previewLoader.value = false;
  }
}

async function setQuotationDetails() {
  if ((!isExternal.value || !noMembersAccess.value) && permissions.value.includes("members-list")) {
    await updateChooseableUsers();
  } else {
    quotationDetails.value.customer = props.quotation.customer
      ? {
          id: props.quotation.customer.id,
          name:
            props.quotation.customer.profile.first_name +
            " " +
            props.quotation.customer.profile.last_name,
          email: props.quotation.customer.email,
        }
      : null;
  }

  await nextTick();
  multipleAddresses.value = props.quotation.delivery_type === "multiple";

  quotationDetails.value.differentInvoice =
    props.quotation.invoice_address?.id !== props.quotation.delivery_address?.id;

  quotationDetails.value.deliveryMethod = props.quotation.delivery_method;

  quotationDetails.value.customerAddress = props.quotation.delivery_address
    ? props.quotation.delivery_address
    : null;

  chosenTeam.value = props.quotation.delivery_address?.team_id
    ? props.quotation.delivery_address.team_id
    : null;

  quotationDetails.value.invoiceAddress = props.quotation.invoice_address
    ? props.quotation.invoice_address
    : null;

  quotationDetails.value.reference = props.quotation.reference;
  quotationDetails.value.note = props.quotation.note;

  quotationDetails.value.mails = props.quotation.mails;

  await nextTick();
  setWarnings();
  isInitializing.value = false;
}

watch(
  () => props.quotation,
  () => {
    setQuotationDetails();
  },
  { immediate: true, deep: true },
);

watch(
  () => quotationDetails.value.customer,
  (newValue, oldValue) => {
    if (isInitializing.value) return;
    if (newValue?.id === oldValue?.id) return;
    quotationDetails.value.customerAddress = null;
    quotationDetails.value.invoiceAddress = null;
    chosenTeam.value = null;
    deliveryAddresses.value = newValue?.addresses;
    teams.value = newValue?.teams;
  },
);

watch(
  () => multipleAddresses.value,
  () => {
    if (isInitializing.value) return;
    if (!multipleAddresses.value) return;
    if (quotationDetails.value.customerAddress) {
      quotationDetails.value.invoiceAddress = quotationDetails.value.customerAddress;
    }
    quotationDetails.value.customerAddress = null;
    chosenTeam.value = null;
  },
);

watch(
  () => quotationDetails.value.differentInvoice,
  (newValue, oldValue) => {
    if (isInitializing.value) return;
    if (oldValue === false) return;
    if (newValue) {
      quotationDetails.value.invoiceAddress = null;
    }
  },
);

watch(
  () => chosenTeam.value,
  (newValue, oldValue) => {
    if (isInitializing.value) return;
    if (newValue === oldValue) return;
    quotationDetails.value.customerAddress = null;
    quotationDetails.value.invoiceAddress = null;
  },
);

watch(
  () => quotationDetails.value.deliveryMethod,
  async (newValue, oldValue) => {
    if (isInitializing.value) return;
    if (newValue === oldValue) return;

    quotationDetails.value.customerAddress = null;

    if (newValue === "pickup") {
      quotationDetails.value.differentInvoice = true;
    }
  },
);

/**
 * The business logic for saving the quotationDetails.
 * Please keep everything below this ONLY for the business
 * logic regarding saving states.
 */
async function update(data) {
  try {
    saving.value = true;
    await quotationRepository.updateQuotation(props.salesId, data);
  } catch (err) {
    handleError(err);
  } finally {
    saving.value = false;
    handleDetailsUpdated();
  }
}
const { debounced: debouncedUpdate, execute: executeUpdate } = useDebounce(
  update,
  config.public.formSaveDebouncetime,
);
const handleInstantSave = () => executeUpdate();
const updateWithSaver = (data, useDebounce = false) =>
  useDebounce ? debouncedUpdate(data) : update(data);

watch(
  watchedQuotationDetails,
  (newValue, oldValue) => {
    if (isInitializing.value) return;

    let updatedProperty = null;
    for (const key in newValue) {
      if (!isEqual(newValue[key], oldValue[key])) {
        updatedProperty = { key, value: newValue[key] };
        break;
      }
    }

    if (updatedProperty === null) return;

    if (updatedProperty.key === "customer") {
      if (newValue.customer?.id === oldValue.customer?.id) return;
      updateWithSaver({ user_id: newValue.customer?.id, ctx_id: newValue.customer?.ctx[0]?.id });
    }

    if (updatedProperty.key === "deliveryMethod") {
      const pickup = newValue.deliveryMethod === "pickup";
      updateWithSaver({ delivery_pickup: pickup });
    }

    if (updatedProperty.key === "customerAddress" && newValue.customerAddress) {
      updateWithSaver({
        address: newValue.customerAddress.id,
      });
    }

    if (updatedProperty.key === "invoiceAddress" && newValue.invoiceAddress) {
      updateWithSaver({
        invoice_address: newValue.invoiceAddress.id,
      });
    }

    if (updatedProperty.key === "reference") {
      updateWithSaver({ reference: newValue.reference }, true);
    }

    if (updatedProperty.key === "note") {
      updateWithSaver({ note: newValue.note }, true);
    }

    setWarnings();
  },
  { deep: true },
);

onMounted(() => {
  leaving.value = false;
})

watch(
  () => multipleAddresses.value,
  (newValue, oldValue) => {
    if (isInitializing.value) return;
    if (newValue === oldValue) return;
    if (leaving.value) return;
    updateWithSaver({ delivery_multiple: newValue });
  },
);
</script>
