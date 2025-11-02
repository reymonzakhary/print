<template>
  <div class="absolute inset-0 grid place-items-center">
    <div class="stack -mt-[3%]">
      <PrindustryLogo class="h-16 text-white" />
      <UILoader class="text-[8rem]" icon-type="fat" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: false,
    default: false,
  },
});
</script>
