<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("import") }} </span>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <input
          id="CustomProductImport"
          type="file"
          name="CustomProductImport"
          @change="importFile($event)"
        />
      </section>
    </template>

    <template #confirm-button> </template>
  </ConfirmationModal>
</template>

<script>
export default {
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  methods: {
    importFile(event) {
      const files = event.target.files || event.dataTransfer.files;
      if (!files.length) {
        return;
      }

      const data = new FormData();

      // add file
      data.append("file", files[0], files[0].name);
      data.append("type", files[0].type);

      this.api
        .uploadFile(`custom/products/import`, data)
        .then(() => {
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.$emit("onClose");
    },
  },
};
</script>
