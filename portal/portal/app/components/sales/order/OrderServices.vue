<template>
  <div>
    <SalesModalService
      v-if="showServiceModal"
      :disabled="saving"
      @on-close="showServiceModal = false"
      @on-add-service="handleAddService"
    />
    <h2 class="my-2 text-xs font-bold tracking-wide uppercase">
      {{ $t("services") }}
    </h2>
    <u class="no-underline list-none">
      <li v-for="service in services" :key="service.id" class="[&:not(:last-child)]:mb-2">
        <SalesService
          type="quotations"
          :service="service"
          :quotation="quotation"
          :may-edit="permissions.includes('orders-update')"
          @on-update="handleUpdateService"
          @on-remove="handleRemoveService"
        />
      </li>
      <li v-if="isEditable && permissions.includes('orders-update')">
        <div class="p-2 text-center bg-gray-200 rounded dark:bg-gray-700">
          <SalesAddButton
            :icon="['fal', 'bell-concierge']"
            class="mr-2"
            variant="secondary"
            @click="showServiceModal = true"
          >
            {{ $t("add service") }}
          </SalesAddButton>
          <SalesAddButton
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
const { isEditable, saving } = storeToRefs(useSalesStore());

const orderRepository = useOrderRepository();

const showServiceModal = ref(false);
const { permissions } = storeToRefs(useAuthStore());
async function handleAddService(newService) {
  try {
    saving.value = true;
    await orderRepository.createService(props.salesId, newService);
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
    await orderRepository.updateService({
      orderId: props.salesId,
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
    await orderRepository.removeServiceFromOrder(props.salesId, serviceId);
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
