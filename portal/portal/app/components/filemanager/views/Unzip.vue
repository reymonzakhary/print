<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal" @on-close="closeModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div class="text-sm font-bold tracking-wide uppercase">
        {{ $t("unzip") }}
      </div>
      <div class="flex items-center justify-between">
        <div class="">
          <input
            id="unzipRadio1"
            v-model.number="createFolder"
            class=""
            type="radio"
            name="uploadOptions"
            value="0"
            checked
          />
          <label class="" for="unzipRadio1">
            {{ $t("to current folder") }}
          </label>
        </div>
        <div class="">
          <input
            id="unzipRadio2"
            v-model.number="createFolder"
            class="form-check-input"
            type="radio"
            name="uploadOptions"
            value="1"
            checked
          />
          <label class="" for="unzipRadio2">
            {{ $t("in new folder") }}
          </label>
        </div>
      </div>

      <transition name="fade">
        <div v-if="createFolder" class="">
          <hr class="my-2" />
          <label for="fm-folder-name" class="mt-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("Folder name") }}</label
          >
          <input
            id="fm-folder-name"
            v-model="directoryName"
            type="text"
            class="px-2 py-0 input"
            :class="{
              'border-orange-500 rounded-b-none': directoryExist,
            }"
            @keyup="validateDirName"
          />
          <div v-show="directoryExist" class="p-1 text-white bg-orange-500 rounded-b">
            {{ $t("folder exists") }}
          </div>
        </div>
      </transition>
      <!-- <span v-else class="text-danger">
				{{ $t("the name already exists") }}
			</span> -->
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm transition-colors rounded-full text-themecontrast-400 bg-theme-400 hover:bg-theme-700"
        :disabled="!submitActive"
        @click="unpackArchive"
      >
        {{ $t("submit") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
// import translate from './../../../mixins/translate';

export default {
  name: "Unzip",
  setup() {
    const { handleError, handleSuccess } = useMessageHandler();
    return { handleError, handleSuccess };
  },
  data() {
    return {
      createFolder: 0,

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
      if (this.createFolder) {
        return this.directoryName && !this.directoryExist;
      }

      return true;
    },
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
     * Unpack selected archive
     */
    unpackArchive() {
      this.$store
        .dispatch("fm/content/unzip", this.createFolder ? this.directoryName : null)
        .then((response) => {
          this.handleSuccess(response);
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
