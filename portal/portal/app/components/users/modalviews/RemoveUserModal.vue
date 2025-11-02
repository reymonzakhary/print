<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>{{ $t("Confirm User Deletion") }}</template>

    <template #modal-body>
      <p>{{ $t("Are you sure you want to delete this user?") }}</p>
    </template>

    <template #confirm-button>
      <ModalButton variant="danger" @click="handleConfirmClick">{{
        $t("Delete")
      }}</ModalButton>
    </template>
  </ConfirmationModal>
</template>

<script>
export default {
  name: "RemoveAddressModal",
  data() {
    return {};
  },
  methods: {
    handleConfirmClick() {
      this.$emit("confirm-delete");
    },
    closeModal() {
      this.$emit("close-modal");
    },
  },
};
</script>
