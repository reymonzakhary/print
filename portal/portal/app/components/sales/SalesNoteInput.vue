<template>
  <section>
    <p
      class="px-2 pt-1 text-xs font-bold tracking-wide uppercase bg-amber-100 dark:bg-amber-300 dark:text-black"
    >
      <font-awesome-icon v-tooltip="'note'" :icon="['fal', 'note-sticky']" />
      {{ $t("internal note") }}
    </p>
    <UITextArea
      :model-value="props.modelValue"
      class="border border-amber-100 hover:!cursor-default text-sm"
      :background-color="`!bg-amber-50 hover:!bg-amber-50 dark:!bg-amber-200 dark:text-amber-900 !shadow-none !rounded-none border-none ${!isEditable ? 'hover:!cursor-default' : ''}`"
      name="notes"
      max-length="100"
      :disabled="!isEditable || disabled"
      @update:model-value="emit('update:modelValue', $event)"
      @blur="emit('blur')"
    />
  </section>
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

const emit = defineEmits(["update:modelValue", "blur"]);

const { isEditable } = storeToRefs(useSalesStore());
</script>
