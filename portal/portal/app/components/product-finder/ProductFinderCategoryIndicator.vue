<template>
  <ProductFinderHeader class="sticky top-0 px-4 py-4">
    <div class="flex items-center gap-3">
      <ProductFinderUICategoryImage :image="image" :title="title" :loading="loading" />
      <div class="mb-0.5 flex-1">
        <SkeletonLine v-if="loading" class="mb-1 !h-5 !w-24" />
        <span v-else class="text-xs font-medium text-theme-600 dark:text-theme-300 2xl:text-sm">
          {{ $t("Current category") }}
        </span>
        <SkeletonLine v-if="loading" class="!h-6 !w-48" />
        <h3 v-else class="text-lg/6 font-semibold text-gray-900 dark:text-gray-100 2xl:text-xl/6">
          {{ title }}
        </h3>
      </div>
    </div>
  </ProductFinderHeader>
</template>

<script setup>
defineProps({
  title: {
    type: [String, undefined],
    required: false,
    default: undefined,
  },
  image: {
    type: [String, undefined],
    required: false,
    default: undefined,
  },
  loading: {
    type: Boolean,
    required: false,
    default: false,
  },
});
</script>
