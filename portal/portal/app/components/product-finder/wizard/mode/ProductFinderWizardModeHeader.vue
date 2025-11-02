<template>
  <div class="mb-6 flex items-center gap-4">
    <ProductFinderUICategoryImage
      size="lg"
      :image="category.image"
      :title="category.title"
      :loading="!category"
    />
    <div class="flex-1">
      <p class="text-sm font-medium text-theme-600 dark:text-theme-300">
        {{ $display_name(category.display_name) }}
      </p>
      <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
        {{ calculatedVariationCount.toLocaleString() }} {{ $t("of maximum") }}
        {{ variationLimit.toLocaleString() }} {{ $t("variations") }}
      </p>
      <!-- Configuration Progress Bar -->
      <div class="relative mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
        <div
          class="h-full bg-prindustry-500 transition-all duration-500 ease-out"
          :style="`width: ${progressPercentage}%; background: linear-gradient(to right, #ec4899, #3095b4, #a855f7, #3095b4)`"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  calculatedVariationCount: {
    type: Number,
    required: true,
  },
  variationLimit: {
    type: Number,
    required: true,
  },
  calculatedAllVariationsCount: {
    type: Number,
    required: true,
  },
});

const progressPercentage = computed(
  () =>
    ((props.calculatedAllVariationsCount - props.calculatedVariationCount) /
      (props.calculatedAllVariationsCount - props.variationLimit)) *
    100,
);
</script>
