<template>
  8<confirmation-modal
    v-if="$store.state.fm.modal.showModal"
    @on-close="closeModal"
  >
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <label for="fm-input-rename">
        <!-- {{ lang.modal.rename.fieldName }} -->
      </label>
      <input
        id="fm-input-rename"
        v-model="name"
        type="text"
        class="w-full px-2 py-1 mx-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        autofocus
        :class="{ 'is-invalid': checkName }"
        @keyup="validateName"
      />
      <div v-show="checkName" class="invalid-feedback">
        <!-- {{ lang.modal.rename.fieldFeedback }} -->
        {{ directoryExist ? ` - directory exists` : "" }}
        {{ fileExist ? ` - file exists` : "" }}
      </div>
    </template>

    <template #confirm-button>
      <!-- :disabled="submitDisable" -->
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-700"
        @click="rename"
      >
        {{ $t("rename") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
// import translate from './../../../mixins/translate';

export default {
  name: "Rename",
  data() {
    return {
      name: "",
      directoryExist: false,
      fileExist: false,
    };
  },
  computed: {
    selectedItem() {
      return this.$store.getters[`fm/content/selectedList`][0];
    },

    checkName() {
      return this.directoryExist || this.fileExist || !this.name;
    },

    submitDisable() {
      return this.checkName || this.name === this.selectedItem.basename;
    },
  },
  mounted() {
    // initiate item name
    this.name = this.selectedItem.basename;
  },
  methods: {
    validateName() {
      if (this.name !== this.selectedItem.basename) {
        // if item - folder
        if (this.selectedItem.type === "dir") {
          // check folder name matches
          this.directoryExist = this.$store.getters[
            `fm/content/directoryExist`
          ](this.name);
        } else {
          // check file name matches
          this.fileExist = this.$store.getters[`fm/content/fileExist`](
            this.name,
          );
        }
      }
    },
    rename() {
      // create new name with path
      const newName = this.selectedItem.dirname
        ? `${this.selectedItem.dirname}/${this.name}`
        : this.name;

      this.$store
        .dispatch("fm/content/rename", {
          type: this.selectedItem.type,
          newName,
          oldName: this.selectedItem.path,
        })
        .then(() => {
          // close modal window
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
          this.closeModal();
        });
    },
    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
