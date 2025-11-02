<template>
  <div class="">
    <p class="mb-1 text-xs font-bold uppercase tracking-wide">
      <font-awesome-icon :icon="['fal', 'message']" />
      {{ $t("reference") }}
    </p>
    <UITextArea
      v-if="isEditable && permissions.includes('orders-update')"
      class="text-sm"
      :model-value="props.modelValue"
      max-length="100"
      :disabled="disabled"
      @update:model-value="emit('update:modelValue', $event)"
      @blur="emit('blur')"
    />
    <div
      v-else-if="!isEditable && props.modelValue?.length > 0"
      class="mt-2 min-h-[3.75rem] px-2 text-sm"
    >
      {{ props.modelValue }}
    </div>
    <div
      v-else
      class="spaces mt-2 min-h-[3.75rem] w-full overflow-hidden break-words text-sm italic text-gray-500"
    >
      {{ $t("no reference") }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [String, null],
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
const { permissions } = storeToRefs(useAuthStore());
const emit = defineEmits(["update:modelValue", "blur"]);

const { isEditable } = storeToRefs(useSalesStore());
</script>
