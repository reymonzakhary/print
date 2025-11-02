<template>
  <UICard class="flex flex-col gap-4 !p-4 empty:hidden" rounded-full>
    <SalesActionButton
      v-if="props.status === statusMap.DRAFT"
      variant="success"
      :disabled="noActionsAllowed || !!warnings.length"
      :icon="['fal', 'file-check']"
      @click="emit('onFinalizeQuotation')"
    >
      {{ $t("Finalize quotation") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="isStatusInGroup(props.status, 'EDITING') && props.status !== statusMap.DRAFT"
      :disabled="noActionsAllowed || !!warnings.length"
      :icon="['fal', 'file-check']"
      @click="emit('onDoneEditing')"
    >
      {{ $t("done editing quotation") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        isStatusInGroup(props.status, isExternal ? 'EXTERNAL_EDITABLE' : 'EDITABLE') &&
        permissions.includes('quotations-update')
      "
      :disabled="noActionsAllowed"
      :icon="['fal', 'file-pen']"
      @click="emit('onEditQuotation')"
    >
      {{ $t("edit quotation") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="
        isStatusInGroup(props.status, 'SENDABLE') &&
        !isExternal &&
        permissions.includes('quotations-create')
      "
      :disabled="noActionsAllowed || !!warnings.length"
      variant="success"
      :icon="['fal', 'paper-plane']"
      @click="emit('onSendQuotation')"
    >
      {{ $t("send to customer") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="mayConvertToOrder"
      :disabled="noActionsAllowed || !!warnings.length"
      :icon="['fal', 'swap']"
      @click="emit('onConvertToOrder')"
    >
      {{ $t("convert to order") }}
    </SalesActionButton>
    <SalesActionButton
      v-if="!isExternal && isStatusInGroup(props.status, 'ACCEPTABLE', 'CANCELABLE')"
      :disabled="noActionsAllowed || !!warnings.length"
      :icon="['fal', 'file-slash']"
      variant="danger"
      @click="emit('onRejectQuotation')"
    >
      {{ $t("reject") }}
    </SalesActionButton>
    <DevOnly>
      <SalesActionButton :disabled="saving" :icon="['fal', 'robot']" @click="handleDevEditStatus">
        {{ $t("[DEV] Edit Status") }}
      </SalesActionButton>
      <SalesActionButton :icon="['fal', 'robot']" @click="saving = !saving">
        {{ $t("[DEV] Toggle Saving") }}
      </SalesActionButton>
    </DevOnly>
    <div
      v-if="isExternal && isStatusInGroup(props.status, ['ACCEPTABLE', 'CANCELABLE'])"
      class="flex"
    >
      <button
        class="w-full rounded-l-full bg-red-500 px-2 py-1 text-sm text-white transition-colors duration-150 hover:bg-red-600"
        :class="{
          '!bg-red-300 text-red-100 hover:!bg-red-300': noActionsAllowed || !!warnings.length,
        }"
        :disabled="noActionsAllowed || !!warnings.length"
        @click="emit('onRejectQuotation')"
      >
        <font-awesome-icon :icon="['fal', 'file-slash']" class="mr-1" />
        {{ $t("reject") }}
      </button>
      <button
        class="w-full rounded-r-full bg-green-500 px-2 py-1 text-sm text-white transition-colors duration-150 hover:bg-green-600"
        :class="{
          '!bg-green-300 text-green-100 hover:!bg-green-300': noActionsAllowed || !!warnings.length,
        }"
        :disabled="noActionsAllowed || !!warnings.length"
        @click="emit('onAcceptQuotation')"
      >
        {{ $t("accept") }}
        <font-awesome-icon :icon="['fal', 'file-check']" class="ml-1" />
      </button>
    </div>
    <ul v-if="warnings.length" class="text-center text-sm">
      <li v-for="warning in warnings" :key="warning" class="[&:not(:first-child)]:mt-2">
        <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="mr-2 text-yellow-400" />
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
});

const emit = defineEmits([
  "onFinalizeQuotation",
  "onDoneEditing",
  "onEditQuotation",
  "onSendQuotation",
  "onAcceptQuotation",
  "onRejectQuotation",
  "onConvertToOrder",
  "onDevEditStatus",
]);

const { statusMap, isStatusInGroup } = useOrderStatus();

const { permissions } = storeToRefs(useAuthStore());
const { saving, isExternal, criticalFlag, warnings } = storeToRefs(useSalesStore());

const noActionsAllowed = computed(
  () => saving.value || criticalFlag.value === "locked" || criticalFlag.value === "trashed",
);

const mayConvertToOrder = computed(
  () =>
    !isExternal.value &&
    isStatusInGroup(props.status, "CONVERTABLE") &&
    permissions.value.includes("orders-list") &&
    permissions.value.includes("orders-create") &&
    permissions.value.includes("orders-read") &&
    permissions.value.includes("orders-access"),
);

function handleDevEditStatus() {
  const statusCode = prompt("Enter status code");
  emit("onDevEditStatus", statusCode);
}
</script>
