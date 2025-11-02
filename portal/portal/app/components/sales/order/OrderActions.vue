<template>
  <UICard class="flex flex-col gap-4 !p-4 empty:hidden" rounded-full>
    <SalesActionButton
      v-if="
        props.showPreviewInvoiceButton &&
        props.status !== statusMap.DRAFT &&
        !isEditable &&
        permissions.includes('invoices-read')
      "
      :disabled="noActionsAllowed"
      :icon="['fal', 'file-check']"
      @click="emit('onPreviewInvoice')"
    >
      {{ $t("Preview Invoice") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        !props.showPreviewInvoiceButton &&
        props.status !== statusMap.DRAFT &&
        props.status !== statusMap.CANCELED &&
        !isEditable &&
        permissions.includes('invoices-create')
      "
      :disabled="noActionsAllowed"
      :icon="['fal', 'file-check']"
      @click="emit('onGenerateInvoice')"
    >
      {{ $t("Generate Invoice") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        props.showPreviewInvoiceButton &&
        props.status !== statusMap.DRAFT &&
        !isArchived &&
        !isEditable &&
        permissions.includes('invoices-create')
      "
      :disabled="noActionsAllowed"
      :icon="['fal', 'sync-alt']"
      @click="emit('onGenerateInvoice')"
    >
      {{ $t("Regenerate Invoice") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="props.status === statusMap.DRAFT && permissions.includes('orders-update')"
      variant="success"
      :disabled="noActionsAllowed || !!warnings.length"
      :icon="['fal', 'file-check']"
      @click="emit('onFinalizeOrder')"
    >
      {{ $t("Finalize order") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="props.showDoneButton && permissions.includes('orders-update')"
      :disabled="saving || criticalFlag === 'locked' || !!warnings.length"
      :icon="['fal', 'file-check']"
      variant="success"
      @click="emit('onSetOrderToDone')"
    >
      {{ $t("set order to done") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        isStatusInGroup(props.status, 'EDITING') && !isArchived && props.status !== statusMap.DRAFT && permissions.includes('orders-update')
      "
      :disabled="saving || criticalFlag === 'locked' || !!warnings.length"
      :icon="['fal', 'file-check']"
      @click="emit('onDoneEditing')"
    >
      {{ $t("done editing order") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        isStatusInGroup(props.status, 'EDITABLE') &&
        !isArchived &&
        isInternal &&
        permissions.includes('orders-update')
      "
      :disabled="saving || criticalFlag === 'locked'"
      :icon="['fal', 'file-pen']"
      @click="emit('onEditQuotation')"
    >
      {{ $t("edit order") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        isStatusInGroup(props.status, 'ARCHIVABLE') &&
        criticalFlag !== 'archived' &&
        permissions.includes('orders-update')
      "
      :disabled="saving || criticalFlag === 'locked'"
      :icon="['fal', 'file-slash']"
      @click="emit('onArchiveOrder')"
    >
      {{ $t("archive order") }}
    </SalesActionButton>
    <DevOnly>
      <SalesActionButton :disabled="saving" :icon="['fal', 'robot']" @click="handleDevEditStatus">
        {{ $t("[DEV] Edit Status") }}
      </SalesActionButton>
      <SalesActionButton :icon="['fal', 'robot']" @click="saving = !saving">
        {{ $t("[DEV] Toggle Saving") }}
      </SalesActionButton>
    </DevOnly>
    <ul v-if="warnings.length" class="text-center text-sm">
      <li v-for="warning in warnings" :key="warning" class="[&:not(:first-child)]:mt-2">
        <font-awesome-icon
          :icon="['fas', 'triangle-exclamation']"
          class="mr-2 text-base text-amber-400"
        />
        <span class="italic text-gray-500">{{ warning }}</span>
      </li>
    </ul>
  </UICard>
</template>

<script setup>
const props = defineProps({
  status: {
    type: [String, Number],
    required: true,
  },
  showDoneButton: {
    type: Boolean,
    default: false,
  },
  isArchived: {
    type: Boolean,
    default: false,
  },
  isInternal: {
    type: Boolean,
    default: true,
  },
  showPreviewInvoiceButton: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "onDoneEditing",
  "onEditQuotation",
  "onSendQuotation",
  "onAcceptQuotation",
  "onRejectQuotation",
  "onArchiveOrder",
  "onSetOrderToDone",
  "onFinalizeOrder",
  "onDevEditStatus",
  "onPreviewInvoice",
  "onGenerateInvoice",
]);

const { permissions } = storeToRefs(useAuthStore());

const { isStatusInGroup, statusMap } = useOrderStatus();

const { saving, criticalFlag, warnings, isEditable } = storeToRefs(useSalesStore());

const noActionsAllowed = computed(
  () => saving.value || criticalFlag.value === "locked" || criticalFlag.value === "trashed",
);

async function handleDevEditStatus() {
  const statusCode = await prompt("Enter status code");
  emit("onDevEditStatus", statusCode);
}
</script>
