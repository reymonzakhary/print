<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div class="flex items-stretch mb-2">
        <input
          id="fm-zip-name"
          v-model="archiveName"
          type="text"
          class="px-2 py-0 rounded-r-none input"
          autofocus
          :placeholder="$t('archive name')"
          :class="{ 'border-orange-500': archiveExist || !submitActive }"
          @keyup="validateArchiveName"
        />
        <span class="p-1 bg-gray-100 rounded-r">.zip</span>
      </div>

      <div v-show="archiveExist" class="text-red-600 bg-red-100">
        {{ $t("archive exist") }}
      </div>
      <hr />
      <p class="mt-2 text-sm font-bold tracking-wide uppercase">
        {{ $t("zip contents") }}
      </p>
      <SelectedFileList />
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white bg-green-500 rounded-full hover:bg-green-700"
        :disabled="!submitActive"
        @click="createArchive"
      >
        {{ $t("zip up") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
export default {
  name: "Zip",
  data() {
    return {
      // name for new archive
      archiveName: "",

      // archive exist
      archiveExist: false,
    };
  },
  computed: {
    /**
     * Submit button - active or no
     * @returns {string|boolean}
     */
    submitActive() {
      return this.archiveName && !this.archiveExist;
    },
  },
  methods: {
    validateArchiveName() {
      if (this.archiveName) {
        this.archiveExist = this.$store.getters[`fm/content/fileExist`](
          `${this.archiveName}.zip`,
        );
      } else {
        this.archiveExist = false;
      }
    },

    createArchive() {
      this.$store.dispatch("fm/content/zip", `${this.archiveName}`).then(() => {
        // close modal window
        this.closeModal();
      });
    },

    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
