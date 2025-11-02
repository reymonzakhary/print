<template>
  <div>
    <Teleport to="body">
      <FileManagerSelectPanel
        v-if="showFileManagerSelectPanel"
        :disk="disk"
        :disk-selector="false"
        @update-value="updateValue($event.path)"
      />
    </Teleport>

    <div
      class="relative grid aspect-[8/3] w-full cursor-pointer items-center rounded-md border-2 border-dashed border-gray-200 bg-contain bg-center bg-no-repeat p-8 text-center text-gray-500 hover:bg-gray-200"
      :style="`background-image: url(${selectedImageBlob})`"
      @click="toggleFileManagerSelectPanel('on')"
    >
      <button
        v-if="selectedImageBlob"
        class="absolute right-1 top-1 h-8 w-8 rounded-full border border-red-500 text-red-500 hover:bg-red-500 hover:text-white"
        @click.stop.prevent="$emit('onImageRemove', '')"
      >
        <font-awesome-icon :icon="['fas', 'trash']" />
      </button>

      <span v-if="!selectedImageBlob">
        <font-awesome-icon :icon="['far', 'image']" class="mr-1" />
        {{ $t("Select image from Asset Manager") }}
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: "UIImageSelector",
  props: {
    disk: {
      required: false,
      type: String,
      default: "assets",
    },
    selectedImage: {
      required: false,
      type: [String, Object],
      default: null,
    },
    fromController: {
      required: false,
      type: Boolean,
      default: false,
    },
  },
  emits: ["onImageSelect", "onImageRemove"],
  setup() {
    const { getMimeTypeFromArrayBuffer, arrayBufferToBase64 } = useUtilities();
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { getMimeTypeFromArrayBuffer, arrayBufferToBase64, api, handleError, handleSuccess };
  },
  data() {
    return {
      showFileManagerSelectPanel: false,
      selectedImageBlob: null,
    };
  },
  watch: {
    selectedImage: {
      immediate: true,
      async handler(v) {
        if (v) {
          if (
            v.toString().startsWith("http://") ||
            v.toString().startsWith("https://") ||
            v.toString().startsWith("data:image/") ||
            v.toString().startsWith("blob:")
          ) {
            this.selectedImageBlob = v;
          } else {
            this.selectedImageBlob = await this.fetchImage(v);
          }
        } else {
          this.selectedImageBlob = null;
        }
      },
    },
  },
  methods: {
    toggleFileManagerSelectPanel(value = null) {
      if (this.fromController) return;
      if (value) {
        this.showFileManagerSelectPanel = value === "on";
      } else {
        this.showFileManagerSelectPanel = !this.showFileManagerSelectPanel;
      }
    },
    async updateValue(path) {
      this.$emit("onImageSelect", path);
      this.toggleFileManagerSelectPanel("off");
    },
    async fetchImage(path) {
      if (typeof path === "object" && path !== null) {
        if (!path.path) return "";
        path = path.path;
      }
      try {
        const response = await this.api.get(
          `/media-manager/file-manager/thumbnails?disk=${this.disk}&path=${path}`,
          { responseType: "arrayBuffer" },
        );

        const mimeType = this.getMimeTypeFromArrayBuffer(response);
        const blob = new Blob([response], { type: mimeType });
        const url = URL.createObjectURL(blob);
        return url;
      } catch (error) {
        console.log("Full error object:", error);

        // Check if error.response exists and has data
        if (error.response && error.response._data) {
          try {
            // Convert ArrayBuffer to text
            const decoder = new TextDecoder("utf-8");
            const errorText = decoder.decode(error.response._data);
            try {
              const errorJson = JSON.parse(errorText);
              this.handleError(errorJson.message);
              return "";
            } catch (jsonError) {
              console.log("Error text (not JSON):", errorText);
            }
          } catch (decodeError) {
            console.log("Could not decode error data:", decodeError);
          }
        }

        this.handleError(error);
        return "";
      }
    },
  },
};
</script>
