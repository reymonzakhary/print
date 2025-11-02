<template>
  <input ref="inputRef" type="text" class="input" :disabled="props.disabled" @blur="emit('blur')" />
</template>

<script setup>
import { useCurrencyInput } from "vue-currency-input";

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    required: true,
  },
  options: {
    type: Object,
    default: () => ({
      locale: "nl-NL",
      currency: "EUR",
    }),
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
});

watch(
  () => props.modelValue,
  (value) => {
    setValue(value);
  },
);

const emit = defineEmits(["update:modelValue", "change", "blur"]);
const { inputRef, setValue } = useCurrencyInput(props.options);
</script>
