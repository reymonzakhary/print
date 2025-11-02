<template>
  <section
    :class="
      cn(
        variant === 'primary' &&
          'rounded-lg p-6 shadow-md shadow-gray-300/50 dark:shadow-gray-900/50',
        gradient && 'bg-gradient-to-b from-gray-50 to-white dark:from-gray-750 dark:to-gray-700',
        !gradient && variant === 'primary' && 'bg-white dark:bg-gray-700',
        !gradient && variant === 'secondary' && 'rounded-md bg-gray-200 p-6 dark:bg-gray-700',
        !gradient &&
          variant === 'theme' &&
          'rounded-md bg-theme-300 p-6 text-themecontrast-400 dark:bg-gray-700',
        props.class,
      )
    "
  >
    <ProductFinderSectionHeader v-if="props.title || props.description || $slots.header">
      <ProductFinderSectionTitle v-if="props.title">{{ props.title }}</ProductFinderSectionTitle>
      <ProductFinderSectionDescription v-if="props.description">
        {{ props.description }}
      </ProductFinderSectionDescription>
      <slot name="header" />
    </ProductFinderSectionHeader>
    <slot />
  </section>
</template>

<script setup>
const props = defineProps({
  title: {
    type: [String, null],
    default: null,
  },
  description: {
    type: [String, null],
    default: null,
  },
  variant: {
    type: String,
    default: "default",
    validator(value) {
      return ["default", "primary", "secondary"].includes(value);
    },
  },
  class: {
    type: String,
    default: "",
  },
  gradient: {
    type: Boolean,
    default: false,
  },
});

const { cn } = useUtilities();
</script>
