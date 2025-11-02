<script setup>
const emit = defineEmits(["selected", "close"]);
</script>

<template>
  <ConfirmationModal classes="w-fit h-5/6" @on-close="emit('close')">
    <template #modal-body>
      <teamCategoryAccessibility @selected="emit('selected', $event)" />
    </template>
  </ConfirmationModal>
</template>

<style scoped></style>
