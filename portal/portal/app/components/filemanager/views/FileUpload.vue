<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal || show" @on-close="closeModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div
        v-show="!progressBar"
        class="relative flex flex-col items-center justify-center border-2 border-gray-400 border-dashed"
      >
        <input
          type="file"
          multiple
          name="myfile"
          class="relative z-50 block w-full h-full p-16 opacity-0 cursor-pointer"
          @change="selectFiles($event)"
        />
        <div class="absolute top-0 left-0 right-0 p-10 m-auto text-center">
          <h4>
            {{ $t("drag files here to upload") }}
            <br />{{ $t("or") }}
          </h4>
          <p class="">{{ $t("select files") }}</p>
        </div>
      </div>

      <div v-if="countFiles" class="py-4">
        <div class="overflow-auto" style="max-height: 150px">
          <div v-for="(item, index) in newFiles" :key="index" class="flex justify-between">
            <div class="mr-4 w-75 text-truncate">
              <font-awesome-icon
                :icon="['fal', mimeToIcon(item.type)]"
                class="mr-1 text-theme-500"
              />
              {{ item.name }}
            </div>
            <div class="text-right">
              {{ bytesToHuman(item.size) }}
            </div>
          </div>
        </div>

        <div class="flex justify-between py-1 my-1 capitalize border-t border-b">
          <div>
            <strong>{{ $t("selected") }}:</strong>
            {{ newFiles.length }}
          </div>
          <div class="text-right">
            <strong>{{ $t("size") }} </strong>
            {{ allFilesSize }}
          </div>
        </div>

        <div class="flex justify-between">
          <div>
            <strong>{{ $t("if the file already exists") }} </strong>
          </div>
          <div class="form-check form-check-inline">
            <input
              id="uploadRadio1"
              v-model="overwrite"
              class="form-check-input"
              type="radio"
              name="uploadOptions"
              value="0"
              checked
            />
            <label class="form-check-label" for="uploadRadio1">
              {{ $t("skip") }}
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input
              id="uploadRadio2"
              v-model="overwrite"
              class="form-check-input"
              type="radio"
              name="uploadOptions"
              value="1"
              checked
            />
            <label class="form-check-label" for="uploadRadio2">
              {{ $t("overwrite") }}
            </label>
          </div>
        </div>
      </div>

      <div v-else class="pt-4 text-center">
        <font-awesome-icon :icon="['fad', 'transporter-empty']" class="text-theme-500 fa-2x" />
        <p>{{ $t("no files") }}</p>
      </div>
      <!-- Progress Bar -->
      <div v-show="countFiles" v-if="progressBar" class="w-full rounded-full shadow bg-grey-light">
        <div
          class="pr-2 text-xs leading-none text-right rounded-full text-themecontrast-400 bg-theme-400"
          :aria-valuenow="progressBar"
          aria-valuemin="0"
          aria-valuemax="100"
          :style="{ width: progressBar + '%' }"
        >
          {{ progressBar }}%
        </div>
      </div>
    </template>

    <template #confirm-button>
      <button
        v-if="!progressBar"
        class="px-4 py-1 mr-2 text-sm transition-colors rounded-full text-themecontrast-400 bg-theme-400 hover:bg-theme-700"
        :class="[countFiles ? 'bg-theme-400' : '']"
        :disabled="!countFiles || loading"
        @click="uploadFiles"
      >
        {{ $t("upload") }}
      </button>
      <button
        v-else
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-gray-300 rounded-full"
        disabled
      >
        {{ $t("upload") }}
        <font-awesome-icon class="mx-1 text-theme-500 fa-spin" :icon="['fad', 'spinner-third']" />
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import { useStore } from "vuex";

export default {
  name: "Upload",
  mixins: [helper],
  props: {
    show: Boolean,
  },
  setup() {
    const { addToast } = useToastStore();
    const { handleError, handleSuccess } = useMessageHandler();
    const store = useStore();
    return { handleError, handleSuccess, store, addToast };
  },
  data() {
    return {
      loading: false,
      newFiles: [],
      overwrite: 1,
    };
  },
  computed: {
    progressBar() {
      return this.store.state.fm.messages.actionProgress;
    },

    countFiles() {
      return this.newFiles.length;
    },

    allFilesSize() {
      let size = 0;

      for (let i = 0; i < this.newFiles.length; i += 1) {
        size += this.newFiles[i].size;
      }

      return this.bytesToHuman(size);
    },
  },
  mounted() {
    this.store.commit("fm/messages/clearProgress");
  },
  methods: {
    selectFiles(event) {
      // files selected?
      if (event.target.files.length === 0) {
        // no file selected
        this.newFiles = [];
      } else {
        // we have file or files
        this.newFiles = event.target.files;
      }
    },

    uploadFiles() {
      this.loading = true;
      // if files exists
      if (this.countFiles) {
        // upload files
        this.store
          .dispatch("fm/content/upload", {
            files: this.newFiles,
            overwrite: this.overwrite,
          })
          .then((response) => {
            // if upload is successful
            if (response && response.result.status === "success") {
              // 	// close modal window
              this.closeModal();
            }
          })
          .catch((error) => {
            this.handleError(error);
            this.closeModal();
          });
      }
    },
    closeModal() {
      if (this.progressBar) {
        return this.addToast({
          type: "info",
          message: this.$t("Please wait until the upload is complete"),
        });
      }
      this.store.commit("fm/modal/clearModal");
    },
  },
};
</script>
