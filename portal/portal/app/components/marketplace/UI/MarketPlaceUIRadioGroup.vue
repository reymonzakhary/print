<template>
  <menu :class="cn('flex items-center', props.class)">
    <UIButton
      v-for="(option, index) in props.options"
      :key="option.value"
      :disabled="option.disabled"
      :class="
        cn(
          'focus:shadow-outline !p-3 focus:outline-none dark:bg-gray-900 dark:hover:bg-gray-950',
          buttonPositionClass(index),
          {
            '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400':
              modelValue === option.value,
            '': modelValue !== option.value,
          },
        )
      "
      variant="neutral-light"
      @click="updateValue(option.value)"
    >
      <font-awesome-icon v-if="option.icon" :icon="option.icon" />
      <span v-if="option.label">{{ $t(option.label) }}</span>
    </UIButton>
  </menu>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    required: true,
  },
  options: {
    type: Array,
    required: true,
    // Each option should have: { value, icon?, label?, disabled? }
  },
  class: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const { cn } = useUtilities();

const updateValue = (value) => {
  emit("update:modelValue", value);
};

const buttonPositionClass = (index) => {
  if (index === 0) return "rounded-none rounded-l border-r dark:border-gray-900";
  if (index === props.options.length - 1)
    return "rounded-none rounded-r-sm border-l dark:border-gray-900";
  return "rounded-none";
};
</script>
