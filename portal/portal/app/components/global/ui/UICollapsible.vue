<template>
  <div
    :class="cn('max-h-0 overflow-hidden transition-all duration-700', show && 'max-h-[10000px]')"
  >
    <slot />
  </div>
</template>

<script setup>
const show = defineModel({ type: Boolean, default: false });
const { cn } = useUtilities();
</script>
