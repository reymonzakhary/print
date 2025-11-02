<template>
  <component :is="as" :class="cn('text-xs font-bold uppercase text-gray-700')">
    <label
      :for="name"
      :class="
        cn(
          'flex w-full cursor-pointer items-center justify-between p-2.5 dark:text-white',
          !disabled && 'hover:bg-gray-100 dark:hover:bg-gray-900',
          disabled && 'cursor-not-allowed',
          labelClass,
        )
      "
    >
      <span>{{ label }}</span>
      <UISwitch :name="name" :value="value" :disabled="disabled" @input="value = $event" />
    </label>
    <slot />
  </component>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  as: {
    type: String,
    required: false,
    default: "li",
  },
  labelClass: {
    type: String,
    required: false,
    default: "",
  },
});

const value = defineModel({ type: Boolean, required: true });

const { cn } = useUtilities();
</script>
