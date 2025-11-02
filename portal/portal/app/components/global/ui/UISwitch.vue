<template>
  <label
    :for="name"
    class="relative flex h-4 w-10 cursor-pointer rounded-full bg-gray-300 transition-all"
    :class="{
      'bg-theme-400': value && variant === 'default',
      'bg-green-400': value && variant === 'success',
      '!cursor-not-allowed opacity-40': disabled,
    }"
  >
    <div
      class="absolute aspect-square h-full rounded-full border-2 bg-white transition-all"
      :class="{
        'left-full -translate-x-full': value,
        'border-theme-400': value && variant === 'default',
        'border-green-400': value && variant === 'success',
        'left-0 translate-x-0 border-gray-300': !value,
      }"
    />
    <input
      :id="name"
      type="checkbox"
      :name="name"
      :checked="!!value"
      :disabled="disabled"
      class="absolute h-0 w-0 appearance-none focus:outline-none active:outline-none"
      @change="handleCheckboxChange"
    />
  </label>
</template>

<script setup>
const props = defineProps({
  value: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  variant: {
    type: String,
    required: false,
    default: "default",
    validator: (value) => ["default", "success"].indexOf(value) !== -1,
  },
  name: {
    type: String,
    required: false,
    default: "checkythecheckbox",
  },
});

const emit = defineEmits(["input"]);

const handleCheckboxChange = () => {
  if (props.disabled) return;
  emit("input", !props.value);
};
</script>
