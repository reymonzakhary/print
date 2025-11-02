<template>
  <div class="relative flex transition">
    <span
      v-if="prefix"
      v-tooltip="prefix"
      class="flex items-center rounded-l border border-gray-200 bg-gray-200 px-2 py-1 text-sm italic text-gray-500 dark:border-gray-500 dark:bg-gray-500 dark:text-gray-200"
      :class="{ 'max-w-[50%]': !affix || !prefix, 'max-w-[33%]': affix && prefix }"
    >
      <font-awesome-icon v-if="Array.isArray(prefix)" class="mr-1" :icon="prefix" />
      <span v-else class="truncate">
        {{ prefix }}
      </span>
    </span>
    <input
      :id="name"
      :name="name"
      :type="type"
      :min="min"
      :max="max"
      :step="step"
      :value="inputValue"
      :placeholder="placeholder"
      class="w-full min-w-4 rounded border bg-white p-2 font-normal text-black shadow-inner ring-theme-100 transition-all duration-100 hover:bg-gray-50 focus:border-theme-400 focus:outline-none focus:ring disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-900 dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
      :class="[
        {
          'py-1': scale === 'sm',
          'py-2': scale === 'md',
          'rounded-l-none': prefix,
          'rounded-r-none': affix,
          'border-1 border-red-500 text-red-500': errors.length > 0,
          'cursor-not-allowed bg-gray-100 hover:bg-gray-100': disabled,
        },
        inputClass,
      ]"
      :disabled="disabled"
      @change="handleChange"
      @input="handleChange"
      @blur="handleTheBlur"
      @focus="$event.target.select()"
    />
    <font-awesome-icon
      v-if="icon.length > 0"
      class="absolute right-0 top-1/2 mr-2 origin-center -translate-y-1/2 text-gray-600 dark:text-gray-300"
      :class="{ 'text-red-600': errors.length > 0 }"
      :icon="icon"
    />
    <span
      v-if="affix"
      v-tooltip="affix"
      class="flex items-center rounded-r border border-gray-200 bg-gray-200 px-2 py-1 text-sm italic text-gray-500 dark:border-gray-500 dark:bg-gray-500 dark:text-gray-200"
      :class="{ 'max-w-[50%]': !affix || !prefix, 'max-w-[33%]': affix && prefix }"
    >
      <font-awesome-icon v-if="Array.isArray(affix)" class="ml-1 aspect-square" :icon="affix" />
      <div v-else class="truncate text-nowrap">
        {{ affix }}
      </div>
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [String, Number, undefined],
    default: undefined,
  },
  rules: {
    type: [String, Object, undefined],
    default: undefined,
  },
  scale: {
    type: [String, Number],
    default: "sm",
  },
  placeholder: {
    type: [String, Number],
    default: "Filter",
  },
  name: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: "text",
  },
  icon: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  prefix: {
    type: [String, Array],
    default: "",
  },
  affix: {
    type: [String, Array],
    default: "",
  },
  min: {
    type: [String, Number],
    default: "",
  },
  max: {
    type: [String, Number],
    default: "",
  },
  step: {
    type: [String, Number],
    default: "",
  },
  inputClass: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["blur", "focus", "update:modelValue"]);

function handleTheBlur(event) {
  emit("blur", event);
  return handleBlur(event);
}

const name = toRef(props, "name");
const {
  value: inputValue,
  handleBlur,
  handleChange,
  errors,
} = useField(name, props.rules, { syncVModel: true });
</script>
