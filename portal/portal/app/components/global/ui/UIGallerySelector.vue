<template>
  <div>
    <FileManagerSelectPanel
      v-if="showFileManagerSelectPanel"
      :disk="disk"
      :disk-selector="false"
      multiple
      @update-value="updateValue($event.path)"
    />

    <div>
      <div class="flex gap-4 overflow-x-scroll flex-nowrap">
        <div
          v-if="selectedImages.length <= limit"
          class="grid w-32 h-32 p-4 overflow-hidden text-gray-300 border-2 border-gray-300 border-dashed rounded cursor-pointer place-items-center shrink-0 text-ellipsis hover:border-theme-500 focus:border-theme-300 hover:text-theme-300"
          @click="toggleFileManagerSelectPanel"
        >
          <span class="text-2xl">
            <font-awesome-icon :icon="['far', 'image']" class="mr-1" />
            <font-awesome-icon :icon="['fal', 'plus']"
          /></span>
        </div>

        <div
          v-for="(url, index) in previews"
          :key="index"
          class="w-32 h-32 p-4 overflow-hidden bg-center bg-no-repeat bg-cover border border-gray-300 rounded cursor-pointer shrink-0 text-ellipsis hover:border-theme-500 focus:border-theme-300"
          :style="`background-image: url(${url})`"
          @click="removeImage(index)"
        ></div>
      </div>
      <span v-if="previews.length > 2" class="inline-block float-right mt-1 text-sm text-gray-500">
        {{ $t("Scroll for more") }}
        <font-awesome-icon :icon="['fal', 'arrow-right']" class="inline" />
      </span>
    </div>
  </div>
</template>

<script>
/** TODO: ReFactor so the onImageRemove, onImageSelect and onInitialized 
//  are all managed from here? or some composable 
//  and that only the state is managed from the parent component.
*/
/**
 * 		handleInitialized(images) {
 *			this.selectedImages = images;
 *		},
 *		handleImageRemove(imageId) {
 *			this.selectedImages = this.selectedImages.filter((item) => item.id !== imageId);
 *		},
 *		handleImageSelect(image) {
 *			this.selectedImages.push(image);
 *		},
 */

export default {
  name: "UIGallerySelector",
  props: {
    disk: {
      required: false,
      type: String,
      default: "assets",
    },
    selectedImages: {
      required: false,
      type: Array,
      default: () => [],
    },
    limit: {
      required: false,
      type: Number,
      default: 9999,
    },
  },
  emits: [
    "onImageRemove",
    "onImageSelect",
    "onInitialized",
    "onSingleImageSelect",
    "onSingleImageRemove",
  ],
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      showFileManagerSelectPanel: false,
      previews: [],
      initialized: false,
    };
  },
  watch: {
    selectedImages: {
      immediate: true,
      handler: async function (val) {
        if (typeof val !== "object" || val === null) return;

        await Promise.all(
          val.map(async (path, index) => {
            try {
              if (!this.initialized) this.addLoader();
              const fetchedImage = await this.fetchImage(path);
              const updatedPreviews = [...this.previews];
              updatedPreviews[index] = fetchedImage;
              this.previews = updatedPreviews;
            } catch (error) {
              console.error(error);
            }
          }),
        );
        this.initialized = true;
      },
    },
  },
  methods: {
    toggleFileManagerSelectPanel() {
      this.showFileManagerSelectPanel = !this.showFileManagerSelectPanel;
    },
    async updateValue(path) {
      this.toggleFileManagerSelectPanel();
      this.$emit("onSingleImageSelect", path);
    },
    removeImage(index) {
      const updatedPreviews = [...this.previews];
      updatedPreviews.splice(index, 1);
      this.previews = updatedPreviews;
      this.$emit("onSingleImageRemove", index);
    },
    async fetchImage(path) {
      try {
        const response = await this.api.get(
          `/media-manager/file-manager/thumbnails?disk=${this.disk}&path=${path}`,
          { responseType: "arraybuffer" },
        );
        const blob = new Blob([response.data], {
          type: response.headers["content-type"],
        });
        const urlCreator = window.URL || window.webkitURL;
        const imageUrl = urlCreator.createObjectURL(blob);
        return imageUrl;
      } catch (error) {
        this.handleError(error);
      }
    },
    addLoader() {
      this.previews = [...this.previews, "https://i.imgur.com/4yT15sl.gif"];
    },
  },
};
</script>
