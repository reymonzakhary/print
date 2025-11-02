<template>
  <div :key="index" class="flex w-full items-center justify-between">
    <span
      v-tooltip="multipleFiles === false ? file.name : ''"
      class="flex w-full cursor-pointer items-center justify-between py-1 text-sm"
    >
      <Thumbnail
        v-if="thisImage(file.extension ? file.extension : file.ext) && !downloadprogress[index]"
        :disk="file.disk"
        :file="file"
        class="mr-2"
      />

      <font-awesome-icon
        v-else-if="!downloadprogress[index]"
        :icon="['fal', extensionToIcon(file.extension)]"
        class="mr-1"
      />

      <span
        v-if="!multipleFiles && !downloadprogress[index]"
        class="flex w-full items-center justify-between truncate whitespace-nowrap"
        @click.stop="select(file, $event)"
      >
        {{ truncate(file.name, 15) }}

        <span class="ml-1 font-mono text-xs text-gray-400">
          {{ bytesToHuman(file.size) }}
        </span>
      </span>

      <div
        v-else-if="multipleFiles && !downloadprogress[index]"
        class="flex w-full items-end justify-between text-white hover:underline"
        @click.stop="select(file, $event)"
      >
        {{ file.name }}

        <span class="ml-1 font-mono text-xs text-gray-400">
          {{ bytesToHuman(file.size) }}
        </span>
      </div>

      <div
        v-if="downloadprogress[index] > 0"
        class="relative block w-full min-w-max rounded-full bg-gray-200 dark:bg-gray-700"
      >
        <div
          class="bg-animate h-4 rounded-full bg-gradient-to-r from-blue-100 to-blue-400"
          :style="`width: ${downloadprogress[index]}%`"
        />
        <span class="absolute top-0 w-full text-center text-xs text-blue-600 drop-shadow-sm">
          <template v-if="!smallProgressBar">{{ $t("downloading") }}</template>
          {{ downloadprogress[index] }}%
        </span>
      </div>

      <font-awesome-icon
        v-if="editable"
        :icon="['fal', loading ? 'spinner-third' : 'trash-can']"
        class="ml-2 text-sm text-red-500"
        :class="{ 'fa-spin': loading }"
        @click.stop="removeFile(index)"
      />
    </span>
  </div>
</template>

<script>
import { mapState, mapActions, mapMutations, useStore } from "vuex";
import moment from "moment";
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
const { getMimeTypeFromArrayBuffer, arrayBufferToBase64 } = useUtilities();

export default {
  mixins: [helper, managerhelper],
  props: {
    file: {
      type: Object,
      required: true,
    },
    type: {
      type: String,
      required: false,
      default: "items",
    },
    index: {
      type: Number,
      required: true,
    },
    multipleFiles: {
      type: Boolean,
      required: false,
      default: false,
    },
    item: {
      type: Object,
      required: true,
    },
    order_id: {
      type: Number,
      required: true,
    },
    editable: {
      type: Boolean,
      required: false,
      default: false,
    },
    smallProgressBar: {
      type: Boolean,
      required: false,
      default: false,
    },
    smallFileName: {
      type: Boolean,
      required: false,
      default: false,
    },
    propDriven: {
      type: Boolean,
      required: false,
      default: false,
    },
    orderType: {
      type: String,
      required: false,
      default: "order",
    },
  },
  emits: ["onImageSelect", "onRemoveFile"],
  setup() {
    const api = useAPI();
    const quotationRepository = useQuotationRepository();
    const store = useStore();
    const { handleError } = useMessageHandler();
    return { api, store, handleError, quotationRepository };
  },
  data() {
    return {
      src: null,
      loading: false,
      preview: false,
      PDFLink: "",
      selectedImage: null,
      showPreview: false,
    };
  },
  computed: {
    ...mapState({
      imageExtensions: (state) => state.fm.settings.imageExtensions,
      selectedDisk: (state) => state.fm.content.selectedDisk,
      order_type: (state) => state.orders.ordertype,
    }),
  },
  methods: {
    ...mapActions({
      refresh_orderdata: "orders/refreshQuotations",
      selectDisk: "fm/filemanager/selectDisk",
      refresh_order: "orders/refreshOrder",
      refresh_quotation: "orders/refreshQuotation",
    }),
    ...mapMutations({
      change_single_item: "orders/change_single_item",
      delete__single_item: "orders/delete__single_item",
      update_fileList: "orders/update_fileList",
    }),
    isFilePath(path) {
      const regex = /[^/]+\.[^/]+$/;
      return regex.test(path);
    },
    select(file, $event) {
      if (!file.extension) {
        file.extension = file.ext;
      }

      const path = this.isFilePath(file.path) ? file.path : `${file.path}/${file.name}`;
      const normalizedPath = path.replace(/\/+/g, "/");

      file.path = normalizedPath;

      this.$emit("onImageSelect", file);
      this.selectItem("files", normalizedPath, $event);
      this.selectAction(normalizedPath, file.extension, file.disk);
    },
    truncate: (str, len) => (str.length > len ? `${str.substring(0, len)}...` : str),
    previewFile(file, e) {
      if (file.path.indexOf("/") === 0) {
        file.path = file.path.substr(file.path.indexOf("/") + 1);
      }

      file.path = file.path.replace(/\/\//g, "/");

      this.selectDisk({ disk: file.disk });
      this.selectItem("files", file.path, e);

      if (file?.extension?.toLowerCase() === "pdf") {
        this.api
          .get(`media-manager/file-manager/download?disk=${file.disk}&path=${file.path}`, {
            responseType: "arrayBuffer",
          })
          .then((res) => {
            const fileURL = window.URL.createObjectURL(
              new Blob([res.data], { type: "application/pdf" }),
            );
            this.PDFLink = fileURL;
          });
      }

      this.loadImage();
      this.preview = true;
    },
    removeFile() {
      if (this.type === "quotations") {
        this.quotationRepository
          .deleteQuotationMedia(this.order_id, this.file.id)
          .then(() => {
            this.$emit("onRemoveFile", this.file.id);
          })
          .catch((error) => {
            this.handleError(error);
          });
      }

      // Same goes for orders

      if (this.type === "items") {
        const theOrderType = this.propDriven ? this.orderType : this.order_type;
        this.api
          .delete(`${theOrderType}s/${this.order_id}/items/${this.item.id}/media/${this.file.id}`)
          .then(() => {
            if (this.propDriven) {
              this.loading = false;
              return this.$emit("onRemoveFile", this.file.id);
            }

            if (theOrderType === "quotation") {
              this.refresh_quotation(this.order_id);
            } else {
              this.refresh_order(this.order_id);
            }
            this.delete__single_item(this.file.id);
          })
          .catch((error) => {
            this.handleError(error);
          });
      }

      // handle delete in case delete is called from order files
      if (this.type === "orders") {
        this.api
          .delete(`orders/${this.order_id}/media/${this.file.id}`)
          .then(() => {
            if (this.propDriven) {
              this.loading = false;
              return this.$emit("onRemoveFile", this.file.id);
            }
            this.refresh_order(this.order_id);
            this.delete__single_item(this.file.id);
          })
          .catch((error) => {
            this.handleError(error);
          });
      }
    },
    async loadImage() {
      await this.api
        .get(`media-manager/file-manager/preview?disk=${this.file.disk}&path=${this.file.path}`, {
          responseType: "arrayBuffer",
        })
        .then((response) => {
          const mimeType = getMimeTypeFromArrayBuffer(response);
          const imgBase64 = arrayBufferToBase64(response);
          this.src = `data:${mimeType};base64,${imgBase64}`;
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.preview = !this.preview;
    },
    download(file) {
      const theFile = file ?? this.file;
      const link = document.createElement("a");
      link.href = file.url;

      const array = theFile.name.split("."),
        name = array[0],
        ext = array[1];

      link.download = name + "-edited-" + moment() + "." + ext;
      link.click();
    },

    thisImage(extension) {
      // extension not found
      if (!extension || !this.imageExtensions) return false;
      return this.imageExtensions.includes(extension.toLowerCase());
    },
  },
};
</script>
