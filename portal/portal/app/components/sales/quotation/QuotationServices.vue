<template>
  <div>
    <SalesModalService
      v-if="showServiceModal"
      :disabled="saving"
      @on-close="showServiceModal = false"
      @on-add-service="handleAddService"
    />
    <h2 class="my-2 text-xs font-bold uppercase tracking-wide">
      {{ $t("services") }}
    </h2>
    <u class="list-none no-underline">
      <template v-if="permissions.includes('quotations-services-list')">
        <li v-for="service in services" :key="service.id" class="[&:not(:last-child)]:mb-2">
          <SalesService
            type="quotations"
            :may-delete="permissions.includes('quotations-services-delete')"
            :may-edit="permissions.includes('quotations-services-update')"
            :service="service"
            :quotation="quotation"
            @on-update="handleUpdateService"
            @on-remove="handleRemoveService"
          />
        </li>
      </template>
      <li
        v-if="
          isEditable &&
          (permissions.includes('quotations-services-create') ||
            permissions.includes('quotations-discount-create'))
        "
      >
        <div class="rounded bg-gray-200 p-2 text-center dark:bg-gray-700">
          <SalesAddButton
            v-if="permissions.includes('quotations-services-create')"
            :icon="['fal', 'bell-concierge']"
            class="mr-2"
            variant="secondary"
            :disabled="isExternal"
            @click="showServiceModal = true"
          >
            {{ $t("add service") }}
          </SalesAddButton>
          <SalesAddButton
            v-if="permissions.includes('quotations-discount-create')"
            v-tooltip="$t('coming soon')"
            disabled
            :icon="['fal', 'bell-concierge']"
            variant="secondary"
          >
            {{ $t("add discount") }}
          </SalesAddButton>
        </div>
      </li>
    </u>
  </div>
</template>

<script setup>
const props = defineProps({
  salesId: {
    type: [Number, String],
    required: true,
  },
  services: {
    type: Array,
    required: true,
  },
  quotation: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["on-service-added", "on-service-updated", "on-service-removed"]);

const { confirm } = useConfirmation();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { t: $t } = useI18n();
const { isEditable, saving, isExternal } = storeToRefs(useSalesStore());
const { permissions } = storeToRefs(useAuthStore());

const quotationRepository = useQuotationRepository();

const showServiceModal = ref(false);

async function handleAddService(newService) {
  try {
    saving.value = true;
    await quotationRepository.createService(props.salesId, newService);
    showServiceModal.value = false;
    emit("on-service-added", { ...newService });
    addToast({
      type: "success",
      message: $t("service succesfully added"),
    });
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleUpdateService(updatedService) {
  try {
    saving.value = true;
    const service = { ...updatedService };
    service.vat = Number(updatedService.vat);
    await quotationRepository.updateService({
      quotationId: props.salesId,
      serviceId: updatedService.id,
      data: updatedService,
    });
    emit("on-service-updated", updatedService);
    addToast({
      type: "success",
      message: $t("service succesfully updated"),
    });
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

async function handleRemoveService(serviceId) {
  try {
    await confirm({
      title: $t("remove service"),
      message: $t("are you sure you want to remove this service?"),
      confirmOptions: {
        label: $t("remove"),
        variant: "danger",
      },
    });
    saving.value = true;
    await quotationRepository.removeServiceFromQuotation(props.salesId, serviceId);
    emit("on-service-removed", serviceId);
    addToast({
      type: "success",
      message: $t("service succesfully removed"),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    saving.value = false;
  }
}
</script>
