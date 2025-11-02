<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal" @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fad', 'siren-on']" class="mr-1 text-red-600" />
      <span class="pr-8">{{ $store.state.fm.modal.modalName }} - {{ $t("Are you sure?") }}?</span>
    </template>

    <template #modal-body>
      <div v-if="selectedItems.length">
        <!-- {{selectedItems}} -->
        <SelectedFileList />
      </div>
      <div v-else>
        <span class="rounded bg-red-600 px-2 text-white shadow">
          {{ $t("nothing selected") }}
        </span>
      </div>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-red-600 px-4 py-1 text-sm text-white transition-colors hover:bg-red-700"
        @click="!loading ? deleteItems() : ''"
      >
        <font-awesome-icon v-if="loading" :icon="['fad', 'spinner-third']" spin />
        <span v-else>{{ $t("delete items") }}</span>
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
export default {
  name: "Delete",
  setup() {
    const { handleError, handleSuccess } = useMessageHandler();
    return { handleError, handleSuccess };
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    selectedItems() {
      return this.$store.getters["fm/content/selectedList"];
    },
  },
  methods: {
    /**
     * Delete selected directories and files
     */
    async deleteItems() {
      this.loading = true;
      // create items list for delete
      const items = this.selectedItems.map((item) => ({
        path: item.path,
        type: item.type,
      }));

      await this.$store.dispatch("fm/content/delete", items);
      this.closeModal();
    },

    closeModal() {
      this.loading = false;
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
