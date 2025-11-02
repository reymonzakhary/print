<template>
  <div class="relative" :class="{ 'mr-1': basketItemCount }">
    <UIButton
      icon-placement="right"
      :icon="['fas', 'bag-shopping']"
      icon-class="ml-1.5"
      class="px-3"
    >
      <span class="mt-[1px]">{{ $t("Basket") }}</span>
    </UIButton>
    <div
      v-if="basketItemCount > 0"
      :class="[
        'absolute -bottom-1.5 -right-2.5',
        'grid size-5 place-items-center rounded-full text-[0.65rem] shadow-md',
        'bg-gradient-to-br from-gray-500 to-gray-600 font-medium text-white',
      ]"
    >
      {{ basketItemCount }}
    </div>
  </div>
</template>

<script setup>
const productFinderStore = useProductFinderStore();
const basketItemCount = computed(() => productFinderStore.basketItemCount);
</script>
