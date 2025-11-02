<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal" @on-close="closeModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div class="form-group">
        <label for="fm-folder-name">
          {{ $t("give your shiny new folder a name") }}
        </label>
        <input
          id="fm-folder-name"
          ref="createFolderInput"
          v-model="directoryName"
          type="text"
          class="w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          :class="{ 'border-red-500': directoryExist }"
          @keyup="validateDirName"
          @keyup.enter="submitActive ? addFolder : ''"
        />
        <div v-show="directoryExist" class="px-2 mt-2 text-white bg-red-600 rounded shadow">
          {{ $t("give your shiny new folder a name") }}
        </div>
      </div>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
        :disabled="!submitActive"
        @click="addFolder"
      >
        {{ $t("add folder") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
// import translate from './../../../mixins/translate';

export default {
  name: "NewFolder",
  data() {
    return {
      // name for new directory
      directoryName: "",

      // directory exist
      directoryExist: false,
    };
  },
  computed: {
    /**
     * Submit button - active or no
     * @returns {string|boolean}
     */
    submitActive() {
      return this.directoryName && !this.directoryExist;
    },
  },
  mounted() {
    this.$refs.createFolderInput.focus();
  },
  methods: {
    /**
     * Check the folder name if it exists or not.
     */
    validateDirName() {
      if (this.directoryName) {
        this.directoryExist = this.$store.getters[`fm/content/directoryExist`](this.directoryName);
      } else {
        this.directoryExist = false;
      }
    },

    /**
     * Create new directory
     */
    addFolder() {
      this.$store.dispatch("fm/content/createDirectory", this.directoryName).then((response) => {
        // if new directory created successfully
        // if (response.data.result.status === "success") {
        // close modal window
        this.closeModal();
        // }
      });
    },

    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
