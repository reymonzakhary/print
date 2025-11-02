<template>
  <div v-if="isLoading" class="p-1 input">
    <UILoader class="text-base" />
  </div>
  <select
    v-else
    :id="name"
    :name="name"
    :value="inputValue"
    class="px-2 py-1 w-full text-black rounded border dark:text-white dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
    :class="{
      'border-1 border-red-500 text-red-500': errors.length > 0,
      'cursor-not-allowed !bg-gray-100 !hover:bg-gray-100': disabled,
      'bg-red': !disabled,
    }"
    :disabled="disabled"
    @change="handleTheChange"
  >
    <option value="" disabled>
      {{ `Select ${name.charAt(0).toUpperCase() + name.slice(1)}` }}
    </option>
    <option
      v-for="option in options"
      :key="option[optionValue]"
      :value="option[optionValue]"
      :disabled="option.disabled"
    >
      {{
        displayProperty === "display_name"
          ? $display_name(option[displayProperty])
          : option[displayProperty]
      }}
    </option>
  </select>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [Number, String],
    required: false,
  },
  value: {
    type: [Number, String],
    required: false,
  },
  rules: {
    type: [String, Object, undefined],
    default: undefined,
  },
  invalid: {
    type: Boolean,
    default: false,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: "option",
  },
  options: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  displayProperty: {
    type: String,
    default: "label",
  },
  optionValue: {
    type: String,
    default: "value",
  },
});

const emit = defineEmits(["input", "update:modelValue"]);

function handleTheChange(event) {
  emit("input", event.target.value);
  emit("update:modelValue", event.target.value);
  handleChange(event);
}

const name = toRef(props, "name");
const {
  value: inputValue,
  handleChange,
  errors,
} = useField(name, props.rules, {
  initialValue: props.value ? props.value : props.modelValue,
});
</script>
