<template>
  <div>
    <div class="mb-1 flex items-center justify-between">
      <p class="mb-1 text-xs font-bold uppercase tracking-wide">
        {{ $t("payment method") }}
      </p>
    </div>
    <template v-if="(props.withStatus || isEditable) && permissions.includes('orders-update')">
      <div :class="{ 'grid grid-cols-2': props.withStatus }">
        <UIVSelect
          :model-value="props.modelValue"
          :options="payMethods"
          :icon="['fal', 'money-bill-wave']"
          :reduce="(method) => method.value"
          disabled
          :placeholder="$t('select payment method')"
          class="rounded-none rounded-l"
          @update:model-value="emit('update:modelValue', $event)"
        />
        <UIVSelect
          v-if="props.withStatus"
          :model-value="`${props.status}`"
          :options="payStatuses"
          :icon="['fal', 'money-check']"
          :reduce="(method) => method.value"
          :placeholder="$t('select payment status')"
          :disabled="props.disabled"
          class="rounded-none rounded-r"
          @update:model-value="emit('update:status', $event)"
        />
      </div>
    </template>
    <template v-else>
      <p
        v-if="props.modelValue"
        class="bg-gray-100 text-base font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400"
      >
        <font-awesome-icon :icon="['fal', 'money-bill-wave']" class="mr-1 text-gray-400" />
        {{ payMethods.find((method) => method.value === props.modelValue).label }}
      </p>
    </template>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  withStatus: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: "0",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["update:modelValue", "update:status"]);
const { permissions } = storeToRefs(useAuthStore());
const { isEditable } = storeToRefs(useSalesStore());
const payMethods = ref([{ value: "invoice", label: $t("invoice") }]);
const payStatuses = ref([
  { value: "0", label: $t("unpaid") },
  { value: "1", label: $t("paid") },
]);
</script>
