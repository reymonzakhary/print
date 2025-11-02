<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal" @on-close="closeModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div class="form-group">
        <label for="fm-file-name"> {{ $t("file name") }}</label>

        <input
          id="fm-file-name"
          ref="createFileInput"
          v-model="fileName"
          type="text"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          :class="{ 'border-red-500': fileExist }"
          @keyup="validateFileName"
          @keyup.enter="submitActive ? addFolder : ''"
        />

        <transition name="slide">
          <div
            v-show="fileExist"
            class="relative z-0 px-2 pt-2 pb-1 -mt-1 text-center text-white bg-red-600 rounded"
          >
            {{ $t("file exists") }}
          </div>
        </transition>
      </div>
    </template>
    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
        :disabled="!submitActive"
        @click="addFile"
      >
        {{ $t("create new file") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
// import translate from './../../../mixins/translate';

export default {
  name: "NewFile",
  data() {
    return {
      // name for new file
      fileName: "",

      // file exist
      fileExist: false,
    };
  },
  computed: {
    submitActive() {
      return this.fileName && !this.fileExist;
    },
  },
  mounted() {
    this.$refs.createFileInput.focus();
  },
  methods: {
    validateFileName() {
      if (this.fileName) {
        this.fileExist = this.$store.getters[`fm/content/fileExist`](
          this.fileName,
        );
      } else {
        this.fileExist = false;
      }
    },

    /**
     * Create new file
     */
    addFile() {
      this.$store.dispatch("fm/content/createFile", this.fileName).then(() => {
        // if new directory created successfully
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
