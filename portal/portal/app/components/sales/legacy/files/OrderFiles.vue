<template>
  <div>
    <teleport to="body">
      <component
        :is="modalName"
        v-if="selectedImage"
        :all-files="[selectedImage].map((file) => ({ path: file?.path }))"
        :disk="selectedImage?.disk"
        @close="selectedImage = null"
      />
    </teleport>
    <div
      v-if="
        innerObject &&
        innerObject.attachments &&
        innerObject.attachments.length > 0 &&
        !listNotAllowed
      "
      class="flex flex-col items-start justify-end overflow-hidden truncate"
    >
      <VDropdown
        v-if="innerObject.attachments.length > 1"
        offset="4"
        placement="top"
        class="truncate"
        :class="{ 'mb-4': editable, 'self-center': centerText }"
      >
        <button class="text-sm text-theme-500" @click.stop="">
          {{ innerObject.attachments.length }} {{ $t("files added") }}
        </button>
        <template #popper>
          <div class="z-10 w-full divide-y rounded bg-gray-900 p-4 text-white shadow">
            <div v-for="(file, i) in innerObject.attachments" :key="'file-' + i" class="w-full">
              <OrderFilesItem
                :key="'file-' + $store.state.orders.fileList_id"
                v-close-popper
                :file="file"
                :item="innerObject"
                :type="type"
                :index="i"
                :multiple-files="true"
                :order_id="order_id"
                :editable="editable"
                :prop-driven="propDriven"
                :order-type="orderType"
                @on-image-select="handleImageSelect"
                @on-remove-file="handleRemoveFile"
              />
            </div>
          </div>
        </template>
      </VDropdown>

      <template v-for="(file, i) in innerObject.attachments">
        <OrderFilesItem
          v-if="innerObject.attachments.length < 2"
          :key="'file-' + i"
          class="py-1"
          :class="{ '-mt-2 mb-2': editable, '-my-2': !editable }"
          :file="file"
          :multiple-files="false"
          :type="type"
          :index="i"
          :item="innerObject"
          :order_id="order_id"
          :editable="editable"
          :small-progress-bar="smallProgressBar"
          :prop-driven="propDriven"
          :order-type="orderType"
          @on-image-select="handleImageSelect"
          @on-remove-file="handleRemoveFile"
        />
      </template>
    </div>

    <div
      v-else-if="!listNotAllowed"
      v-tooltip="$t('no file added')"
      class="truncate whitespace-nowrap text-center text-sm italic text-orange-500"
      :class="{ 'mb-4': editable }"
    >
      <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-1" />
      {{ $t("no file") }}
    </div>

    <input :id="'fileUpload-' + type + index" hidden type="file" @change="onFileChange" />

    <button
      v-if="
        editable &&
        !ext_connection &&
        !uploadNotAllowed &&
        permissions.includes('media-sources-update')
      "
      class="w-full rounded-full border bg-gray-200 px-2 py-1 text-sm transition-colors duration-75 dark:border-gray-900 dark:bg-gray-800"
      :class="{
        'dark:bg-text-gray-200 bg-gray-100 text-gray-500 dark:bg-gray-500': disabled,
        'hover:bg-gray-300 dark:hover:bg-black': !disabled,
      }"
      :disabled="disabled"
      @click.stop="chooseFiles"
    >
      <font-awesome-icon :icon="['fal', 'upload']" class="mr-1" />
      {{
        innerObject && innerObject.attachments && innerObject.attachments.length === 0
          ? $t("Upload file")
          : $t("Upload another file")
      }}
      <font-awesome-icon
        v-if="loading"
        :icon="['fad', 'spinner-third']"
        class="fa-spin text-theme-500"
      />
    </button>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

// modal views
import Preview from "~/components/filemanager/views/Preview.vue";
import TextEdit from "~/components/filemanager/views/TextEdit.vue";
import AudioPlayer from "~/components/filemanager/views/AudioPlayer.vue";
import PDFViewer from "~/components/filemanager/views/PDFViewer.vue";
import VideoPlayer from "~/components/filemanager/views/VideoPlayer.vue";
import ImageViewer from "~/components/filemanager/views/ImageViewer.vue";
import PreviewLoader from "~/components/filemanager/views/PreviewLoader.vue";
import UIButton from "~/components/global/ui/UIButton.vue";

export default {
  components: {
    Preview,
    TextEdit,
    AudioPlayer,
    PDFViewer,
    VideoPlayer,
    PreviewLoader,
    ImageViewer,
  },
  props: {
    object: Object,
    disabled: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    order_id: {
      type: Number,
      rquired: false,
      default: null,
    },
    index: Number,
    type: {
      type: String,
      rquired: false,
      default: "items",
    },
    editable: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    ext_connection: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    centerText: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    smallProgressBar: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    propDriven: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    orderType: {
      type: String,
      rquired: false,
      default: "order",
    },
    isWholeOrder: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    uploadNotAllowed: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    deleteNotAllowed: {
      type: Boolean,
      rquired: false,
      default: false,
    },
    listNotAllowed: {
      type: Boolean,
      rquired: false,
      default: false,
    },
  },
  emits: ["on-file-uploaded", "on-remove-file"],
  setup() {
    const api = useAPI();
    const quotationRepository = useQuotationRepository();
    const orderRepository = useOrderRepository();
    const { handleError, handleSuccess } = useMessageHandler();
    const { addToast } = useToastStore();
    const { permissions } = storeToRefs(useAuthStore());
    return {
      api,
      handleError,
      handleSuccess,
      quotationRepository,
      orderRepository,
      addToast,
      permissions,
    };
  },
  data() {
    return {
      loading: false,
      selectedImage: null,
      innerObject: this.object,
    };
  },
  computed: {
    ...mapState({
      order: (state) => state.orders.active_order_data,
      modalName: (state) => state.fm.modal.modalName,
    }),
  },
  methods: {
    ...mapMutations({
      change_item: "orders/change_item",
      update_item: "orders/update_item",
      change_single_item: "orders/change_single_item",
      update_fileList: "orders/update_fileList",
    }),
    handleImageSelect(image) {
      if (image !== null) {
        this.selectedImage = image;
      }
    },
    chooseFiles() {
      document.getElementById("fileUpload-" + this.type + this.index).click();
    },
    handleRemoveFile(id) {
      if (this.deleteNotAllowed)
        return this.addToast({
          type: "error",
          message: this.$t("You are not allowed to delete files"),
        });

      if (this.propDriven) {
        this.innerObject.attachments = this.innerObject.attachments.filter(
          (attachment) => attachment.id !== id,
        );
      }
      this.$emit("on-remove-file", id);
    },
    onFileChange(event) {
      this.loading = true;
      const files = event.target.files || event.dataTransfer.files;
      if (!files.length) {
        return;
      }
      this.createFile(files[0]);
    },
    async createFile(file) {
      const theOrderType = this.propDriven ? this.orderType : this.order.type;
      const data = new FormData();

      // add file
      data.append("files[]", file);
      data.append("overwrite", false);

      const config = {
        isFormData: true,
      };

      let uploadMedia;
      if (this.type === "quotations") {
        uploadMedia = this.quotationRepository.uploadQuotationMedia;
      } else if (this.type === "orders") {
        uploadMedia = this.orderRepository.uploadOrderMedia;
      }

      if (this.type === "quotations" || this.type === "orders") {
        try {
          const response = await uploadMedia(this.order_id, data);
          this.innerObject = {
            ...this.innerObject,
            attachments: [...this.innerObject.attachments, response],
          };
          this.$emit("on-file-uploaded", response);
        } catch (err) {
          this.handleError(err);
        } finally {
          this.loading = false;
        }
      }

      if (this.type === "items") {
        this.api
          .post(
            `${theOrderType}s/${this.order_id}/items/${this.innerObject.id}/media`,
            data,
            config,
          )
          .then((response) => {
            if (!this.propDriven) {
              this.update_fileList({
                key: "id",
                value: response.result.fileManager,
                id: this.innerObject.id,
              });
              this.change_single_item({
                key: "attachments",
                value: response.result.fileManager,
                id: this.innerObject.id,
              });
            } else {
              this.$emit("on-file-uploaded", response.result.fileManager[0]);
            }
            this.loading = false;
          })
          .catch((error) => this.handleError(error));
      }
    },
  },
};
</script>
