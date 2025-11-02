<template>
  <div
    class="relative flex overflow-hidden transition bg-gray-200 border border-gray-200 rounded dark:bg-gray-500 dark:border-gray-500"
  >
    <CurrencyInput
      :model-value="props.modelValue"
      class="!py-1 border-none"
      :options="{
        currency: 'EUR',
        locale: $i18n.localeProperties.code,
        autoDecimalDigits: false,
        useGrouping: true,
        precision: 2,
        valueScaling: 'precision',
        hideCurrencySymbolOnFocus: false,
        hideGroupingSeparatorOnFocus: false,
        hideNegligibleDecimalDigitsOnFocus: false,
      }"
      :disabled="props.disabled"
      @update:model-value="emit('update:modelValue', $event)"
      @blur="emit('blur')"
    />
    <div v-if="$slots.affix" class="grid items-center px-2 italic text-gray-500 dark:text-gray-200">
      <slot name="affix" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    required: true,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "blur"]);
</script>
