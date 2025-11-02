<template>
  <div class="relative flex">
    <CurrencyInput
      :model-value="props.modelValue"
      :class="['outline-none', props.inputClass]"
      :options="{
        currency: currencySetting,
        locale: $i18n.localeProperties.code,
        autoDecimalDigits: false,
        useGrouping: true,
        precision: 2,
        valueScaling: 'precision',
        hideCurrencySymbolOnFocus: false,
        hideGroupingSeparatorOnFocus: false,
        hideNegligibleDecimalDigitsOnFocus: false,
        ...props.options,
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
  currency: {
    type: [String, null],
    required: false,
    default: null,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  inputClass: {
    type: [String, Object],
    required: false,
    default: "",
  },
  options: {
    type: Object,
    required: false,
    default: () => ({}),
  },
});

const emit = defineEmits(["update:modelValue", "blur"]);

const authStore = useAuthStore();
const currencySetting = computed(() => {
  if (props.currency) return props.currency;
  // const setting = authStore.settings.data.find((setting) => setting.key === "currency"); // moved to ME, where it has fallback for legacy $ sign
  const setting = authStore.currencySettings;
  return setting ? setting : "EUR"; // fallback to EUR
});
</script>
