<template>
  <div
    :class="{
      'mb-8': variant === 'primary',
      'mb-4': variant === 'secondary',
      'mb-2': variant === 'tertiary',
    }"
  >
    <h2
      :class="{
        'text-3xl font-bold': variant === 'primary',
        'text-xl font-semibold': variant === 'secondary',
        'text-lg font-semibold': variant === 'tertiary',
      }"
    >
      <font-awesome-icon
        :icon="['fal', icon]"
        class="text-theme-400"
        :class="{
          'mr-3': variant === 'primary',
          'mr-2': variant === 'secondary',
          'mr-1': variant === 'tertiary',
        }"
      />
      {{ title }}
    </h2>
    <p v-if="description" class="mt-2">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: "hammer-brush",
  },
  title: {
    type: String,
    default: "Studio",
  },
  description: {
    type: String,
    default: null,
  },
  variant: {
    type: String,
    required: false,
    default: "primary",
    validator: (value) => ["primary", "secondary", "tertiary"].includes(value),
  },
});
</script>
