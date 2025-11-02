<template>
  <VDropdown placement="bottom-end">
    <ProductFinderOrderButton />

    <template #popper>
      <ProductFinderBasket />
    </template>
  </VDropdown>
  <ProductFinderOrderModal @close="handleClose" @add-to-basket="handleAddToBasketEvent" />
</template>

<script setup>
const productFinderStore = useProductFinderStore();

const handleClose = () => productFinderStore.closeBasketDialog();

const handleAddToBasketEvent = (_itemFromModal) => {
  // If you ever need to do something after the basket adding event do it here
};
</script>
