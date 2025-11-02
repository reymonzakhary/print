<template>
  <component :is="props.is" class="mb-2 text-xs font-bold tracking-wide uppercase">
    <slot />
  </component>
</template>

<script setup>
const props = defineProps({
  is: {
    type: String,
    required: false,
    default: "h2",
  },
});
</script>
