<template>
  <div>
    <div
      class="fixed bottom-0 left-0 top-0 z-50 flex h-full w-full items-center justify-center bg-black"
      :style="`background:${bgColor}`"
    >
      <button
        class="absolute right-0 top-0 m-4 flex items-center text-white"
        @click="($store.commit('fm/modal/clearModal'), $emit('onClose'))"
      >
        {{ $t("close") }}
        <font-awesome-icon :icon="['fad', 'circle-xmark']" class="ml-2" />
      </button>

      <div class="editor-container p-12 text-white">
        <button
          v-if="!cropping && !filtering"
          class="fixed bottom-4 right-32 rounded bg-gray-400 p-2 text-center text-themecontrast-400 transition-colors duration-75 hover:bg-gray-500 z-50"
          @click="download()"
        >
          <font-awesome-icon :icon="['fal', 'image']" />
          {{ $t("download") }}
        </button>

        <button
          v-if="!cropping && !filtering"
          class="fixed right-4 top-16 rounded bg-theme-400 p-2 text-center text-themecontrast-400 transition-colors duration-75 hover:bg-theme-500 z-50"
          @click="resetImage()"
        >
          <font-awesome-icon :icon="['fal', 'image']" />
          {{ $t("reset image") }}
        </button>

        <button
          v-if="!cropping && !filtering"
          class="fixed bottom-4 right-4 rounded bg-green-600 p-2 text-center text-white transition-colors duration-75 hover:bg-green-700 z-50"
          @click="openSaveAsDialog()"
        >
          <font-awesome-icon :icon="['fal', 'save']" />
          {{ $t("save as") }}
        </button>

        <!-- FILTERS! -->
        <section>
          <img
            v-show="filtering && !cropping"
            v-if="imgSrc"
            id="EditableImage"
            ref="img"
            :src="imgSrc"
            alt="Preview"
            style="max-width: 80vw; max-height: 80vh; min-height: 60vh"
            :style="{
              filter: `
                grayscale(${filterObject.grayscale}) 
                sepia(${filterObject.sepia}) 
                saturate(${filterObject.saturate}) 
                invert(${filterObject.invert}) 
                brightness(${filterObject.brightness}) 
                contrast(${filterObject.contrast}) 
                hue-rotate(${filterObject.hueRotate}deg) 
                blur(${filterObject.blur}px)
              `,
            }"
            crossOrigin="Anonymous"
          />
          <font-awesome-icon v-else :icon="['fad', 'image-landscape']" class="p-12 text-9xl" fade />

          <button
            class="fixed left-4 top-16 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500 z-50"
            :class="{ 'bg-theme-400 hover:bg-theme-500': filtering }"
            @click="((filtering = !filtering), stopCropping(), (comparing = false))"
          >
            <font-awesome-icon :icon="[filtering ? 'fad' : 'fal', 'sliders-h']" />
          </button>

          <section class="fixed right-4 top-24 flex z-50">
            <section
              v-if="filtering"
              class="flex flex-col justify-around"
              style="height: 80vh"
              transition="fade"
            >
              <span v-for="(filter, key) in filterObject" :key="key" class="my-1 p-2">
                <span
                  v-if="key !== 'suffix'"
                  class="flex w-40 flex-wrap items-center justify-between"
                >
                  <p class="w-full">{{ key }}</p>
                  <input
                    v-model="filterObject[key]"
                    class="flex h-3 w-24 appearance-none overflow-hidden rounded-lg bg-white"
                    type="range"
                    :max="
                      key === 'hueRotate'
                        ? '360'
                        : key === 'brightness'
                          ? '3'
                          : key === 'saturate'
                            ? '20'
                            : key === 'contrast'
                              ? '10'
                              : key === 'blur'
                                ? '50'
                                : '1'
                    "
                    min="0"
                    :step="key === 'hueRotate' ? '1' : '0.01'"
                  />
                  <input
                    v-model="filterObject[key]"
                    type="text"
                    class="flex w-10 rounded bg-gray-800 px-1"
                  />
                </span>
              </span>
              <button
                v-if="filtering"
                class="mx-2 rounded-full bg-green-600 p-2 text-center text-white transition-colors duration-75 hover:bg-green-700"
                @click="addEffect()"
              >
                <font-awesome-icon
                  :icon="['fal', filteringLoader ? 'spinner-third' : 'check']"
                  :class="{ 'fa-spin': filteringLoader }"
                />
                {{ $t("apply effects") }}
              </button>
            </section>
          </section>
        </section>

        <!-- CROPPER -->
        <section>
          <VueCropper
            v-show="!filtering && !comparing"
            v-if="imgSrc"
            ref="cropper"
            :key="imgSrc"
            :src="imgSrc"
            alt="Source Image"
            preview=".preview"
            :auto-crop="false"
            :img-style="{
              display: 'block',
              'max-width': '80vw',
              'max-height': '80vh',
              'min-height': '60vh',
            }"
            drag-mode="move"
            :ready="stopCropping"
          />

          <!-- <div v-show="croppingPreview" class="overflow-hidden absolute top-0 right-0 mt-12 -mr-48 w-48 h-48 text-white preview"></div> -->

          <transition name="slide" class="flex flex-col">
            <span v-show="cropping">
              <span class="absolute mt-1 flex text-xs text-gray-700">
                <p>
                  <font-awesome-icon :icon="['fal', 'search']" />
                  {{ $t("scroll to zoom") }}
                </p>
                <p class="ml-4">
                  <font-awesome-icon :icon="['fal', 'arrows-up-down-left-right']" />
                  {{ $t("drag image to move") }}
                </p>
                <p class="ml-4">
                  <font-awesome-icon :icon="['fal', 'ruler-combined']" />

                  {{ $t("maintain aspect ratio") }}
                </p>
              </span>

              <!-- <p class="absolute top-0 right-0 mt-6 -mr-48 w-48 text-white">preview</p> -->

              <div class="fixed right-4 top-24 w-48 overflow-hidden text-white z-50">
                <span />

                <span v-if="cropImg">
                  <p class="mt-6 text-xs font-bold uppercase tracking-wide">
                    {{ $t("result") }}
                  </p>
                  <img :src="cropImg" alt="Cropped Image" class="w-full" style="max-height: 60vh" />
                  <button class="text-theme-500" @click="createImage()">
                    {{ $t("continue with this image") }}
                  </button>
                </span>
              </div>
            </span>
          </transition>

          <span class="fixed left-4 top-28 flex flex-col z-50">
            <button
              class="h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              :class="{ 'bg-theme-400 hover:bg-theme-500': cropping }"
              @click="cropping ? stopCropping() : startCropping()"
            >
              <font-awesome-icon :icon="[cropping ? 'fad' : 'fal', 'crop-simple']" />
            </button>

            <!-- <transition-group name="slide" class="flex flex-col"> -->
            <button
              v-show="cropping"
              key="1"
              class="mt-2 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              @click="rotate(45)"
            >
              <font-awesome-icon :icon="['fal', 'arrow-rotate-right']" />
            </button>

            <button
              v-show="cropping"
              key="2"
              class="mt-2 h-10 w-10 bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              @click="rotate(-45)"
            >
              <font-awesome-icon :icon="['fal', 'arrow-rotate-left']" />
            </button>

            <button
              v-show="cropping"
              ref="flipX"
              key="3"
              class="mt-2 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              @click="flipX()"
            >
              <font-awesome-icon :icon="['fal', 'reply']" />
            </button>

            <button
              v-show="cropping"
              ref="flipY"
              key="4"
              class="mt-2 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              @click="flipY()"
            >
              <font-awesome-icon :icon="['fal', 'reply']" class="fa-rotate-90" />
            </button>

            <button
              v-show="cropping"
              class="mt-2 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500"
              @click="reset()"
            >
              <font-awesome-icon :icon="['fal', 'rotate']" />
            </button>

            <button
              v-show="cropping"
              class="mt-2 h-10 w-10 bg-green-600 p-2 text-center text-white transition-colors duration-75 hover:bg-green-700"
              @click="(croppingLoader, cropImage())"
            >
              <font-awesome-icon
                :icon="!croppingLoader ? ['fal', 'check'] : ['fal', 'spinner-third']"
                :class="{ 'fa-spin': croppingLoader }"
              />
            </button>
            <!-- </transition-group> -->
          </span>
        </section>

        <!-- COMPARE -->
        <section>
          <template v-if="comparing && !filtering && imgSrc">
            <div
              id="comparing"
              class="bg-red-500"
              :style="{ width: imgWidth + 'px', height: imgHeight + 'px' }"
            >
              <VueCompareImage
                :left-image="bkpImgSrc"
                :right-image="imgSrc"
                style="max-height: 100%; max-width: 100%"
              />
            </div>
          </template>

          <button
            class="fixed left-16 top-16 h-10 w-10 rounded bg-gray-400 p-2 text-center text-white transition-colors duration-75 hover:bg-gray-500 z-50"
            :class="{ 'bg-theme-400 hover:bg-theme-500': comparing }"
            @click="((comparing = !comparing), (filtering = false), stopCropping())"
          >
            <font-awesome-icon
              :icon="[comparing ? 'fad' : 'fal', comparing ? 'image' : 'images']"
            />
          </button>
        </section>
      </div>
    </div>

    <!-- Save As Dialog -->
    <ConfirmationModal
      v-if="showSaveAsDialog"
      classes="w-11/12 sm:w-1/2 lg:w-1/3"
      @onClose="closeSaveAsDialog()"
    >
      <template #modal-header>
        <h3 class="text-lg font-semibold">
          {{ $t("save as") }}
        </h3>
      </template>

      <template #modal-body>
        <div class="mb-4">
          <label for="saveAsFileName" class="mb-2 block text-sm font-medium">
            {{ $t("filename") }}
          </label>
          <input
            id="saveAsFileName"
            ref="fileNameInput"
            v-model="saveAsFileName"
            type="text"
            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
            :placeholder="suggestedFileName"
            @keyup.enter="saveAsFile()"
            @keyup.escape="closeSaveAsDialog()"
          />
        </div>
      </template>

      <template #confirm-button>
        <button
          class="rounded-full bg-blue-600 px-4 py-1 text-sm capitalize text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          @click="saveAsFile()"
          :disabled="!saveAsFileName.trim()"
        >
          {{ $t("save") }}
        </button>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script>
// cropper
import VueCropper from "vue-cropperjs";
import now from "moment";
import ConfirmationModal from "~/components/global/ConfirmationModal.vue";

export default {
  components: {
    VueCropper,
    ConfirmationModal,
  },
  props: {
    image: {
      type: Object,
      required: false,
      default: null,
      validator(value) {
        return (
          Object.prototype.hasOwnProperty.call(value, "disk") &&
          Object.prototype.hasOwnProperty.call(value, "path") &&
          Object.prototype.hasOwnProperty.call(value, "name") &&
          Object.prototype.hasOwnProperty.call(value, "extension")
        );
      },
    },
  },
  emits: ["onClose"],
  setup() {
    const { getMimeTypeFromArrayBuffer, arrayBufferToBase64 } = useUtilities();
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { getMimeTypeFromArrayBuffer, arrayBufferToBase64, api, handleError, handleSuccess };
  },
  data() {
    return {
      // imgSrc: require('~/assets/images/mountain2.jpg'),
      imgSrc: "",
      bkpImgSrc: {},
      imgName: "",
      imgType: "",
      imgMimeType: "",
      bgColor: "#000",

      imgWidth: 100,
      imgHeight: 100,

      // cropper
      cropImg: "",
      data: null,
      cropping: false,
      croppingLoader: false,
      croppingPreview: true,

      // image filters
      filtering: false,
      filteringLoader: false,
      show: "",

      // save as dialog
      showSaveAsDialog: false,
      saveAsFileName: "",
      
      // loading state
      isLoadingImage: false,

      filterObject: {
        grayscale: 0,
        sepia: 0,
        saturate: 1,
        hueRotate: 0,
        invert: 0,
        brightness: 1,
        contrast: 1,
        blur: 0,
      },

      // image compare
      comparing: false,
    };
  },
  computed: {
    selectedItem() {
      if (this.image) {
        const sanitizedPath = this.image.path.replace(/\/\//g, "/");
        const img = {
          path: sanitizedPath,
          basename: this.image.name,
          extension: this.image.extension,
          disk: this.image.disk,
        };
        return img;
      } else {
        const files = this.$store.state.fm.content.selected.files;
        return files.map((file) => {
          return {
            path: file,
          };
        })[0];
      }
    },
    selectedDisk() {
      return this.$store.state.fm.content.selectedDisk;
    },
    suggestedFileName() {
      if (this.imgName) {
        const nameWithoutExt = this.imgName.replace(/.jpeg|.png|.svg|.jpg/gi, "");
        const extension = this.imgType == "jpeg" ? "jpg" : this.imgType;
        return `${nameWithoutExt}-edited.${extension}`;
      }
      return "";
    },
  },
  watch: {
    imgSrc(v) {
      this.croppingPreview = false;
      this.imgSrc = v;
    },
    selectedItem(v) {
      this.loadImage();
    },
    cropImg(v) {
      this.croppingLoader = false;
    },
  },
  mounted() {
    if (window.IntersectionObserver) {
      const observer = new IntersectionObserver(
        (entries, obs) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              this.loadImage();
              obs.unobserve(this.$el);
            }
          });
        },
        {
          root: null,
          threshold: "0.5",
        },
      );
      // add observer for template
      observer.observe(this.$el);
    } else {
      //   if (this.selectedItem) {
      this.loadImage();
      //   }
    }
    //  }
  },
  methods: {
    stopCropping() {
      this.cropping = false;
      this.$refs.cropper.clear();
      // this.disableCropping()
    },
    startCropping() {
      this.filtering = false;
      this.comparing = false;
      this.cropping = true;
      this.croppingPreview = true;

      if (this.cropping === true) {
        this.$refs.cropper.enable();
        this.$refs.cropper.initCrop();
      }
    },
    disableCropping() {
      this.croppingPreview = false;
      this.$refs.cropper.disable();
    },
    reset() {
      this.$refs.cropper.reset();
    },
    cropImage() {
      // get image data for post processing, e.g. upload or setting image src
      this.cropImg = this.$refs.cropper.getCroppedCanvas().toDataURL();
    },
    flipX() {
      const dom = this.$refs.flipX;
      let scale = dom.getAttribute("data-scale");
      scale = scale ? -scale : -1;
      this.$refs.cropper.scaleX(scale);
      dom.setAttribute("data-scale", scale);
    },
    flipY() {
      const dom = this.$refs.flipY;
      let scale = dom.getAttribute("data-scale");
      scale = scale ? -scale : -1;
      this.$refs.cropper.scaleY(scale);
      dom.setAttribute("data-scale", scale);
    },
    rotate(deg) {
      this.$refs.cropper.rotate(deg);
    },

    /** set uploaded file to cropper canvas and filter img preview */
    async loadImage() {
      // Prevent multiple simultaneous loads
      if (this.isLoadingImage) {
        return;
      }
      
      if (this.selectedItem) {
        this.isLoadingImage = true;
        
        // Clear previous image data
        this.imgSrc = null;
        this.bkpImgSrc = null;
        this.cropImg = null;
        
        try {
          await this.api
          .get(
            `media-manager/file-manager/preview?disk=${this.image ? this.selectedItem.disk : this.selectedDisk}&path=${this.selectedItem.path}`,
            { responseType: "arrayBuffer" },
          )
          .then((response) => {
            // retreive width and height, please help to do this more efficient...
            const blob = new Blob([response], { type: "image/jpeg" });
            const img = new Image();

            img.src = URL.createObjectURL(blob);
            img.onload = () => {
              // Calculate display dimensions that respect viewport constraints
              const viewportWidth = window.innerWidth * 0.8; // 80vw
              const viewportHeight = window.innerHeight * 0.8; // 80vh
              const minHeight = window.innerHeight * 0.6; // 60vh

              const aspectRatio = img.width / img.height;

              let displayWidth = Math.min(img.width, viewportWidth);
              let displayHeight = displayWidth / aspectRatio;

              if (displayHeight > viewportHeight) {
                displayHeight = viewportHeight;
                displayWidth = displayHeight * aspectRatio;
              }

              // Ensure minimum height
              if (displayHeight < minHeight) {
                displayHeight = minHeight;
                displayWidth = displayHeight * aspectRatio;
              }

              this.imgWidth = Math.round(displayWidth);
              this.imgHeight = Math.round(displayHeight);

              URL.revokeObjectURL(img.src);
            };

            //  set imgSrc
            const mimeType = this.getMimeTypeFromArrayBuffer(response);
            const imgBase64 = this.arrayBufferToBase64(response);
            
            // Add timestamp to force browser to treat as new image
            const timestamp = Date.now();
            const dataUrl = `data:${mimeType};base64,${imgBase64}`;

            this.imgName = this.selectedItem.basename;
            this.imgType = this.selectedItem.extension;
            this.imgMimeType = mimeType;
            this.bkpImgSrc = dataUrl;
            this.imgSrc = dataUrl;

            // The :key="imgSrc" on VueCropper will force a complete re-render

            // this.getBackgroundColor() //commented because unsure
          })
          .catch((error) => {
            this.handleError(error);
          })
          .finally(() => {
            this.isLoadingImage = false;
          });
        } catch (error) {
          this.handleError(error);
          this.isLoadingImage = false;
        }
      }
    },
    /** Create an actual image from the canvas and replace the uploaded one with it */
    createImage() {
      // canvas to image file format
      this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
        const image = URL.createObjectURL(blob); // create a referable image url
        this.imgSrc = image; // replace the preview img (filters) and the canvas image (cropper)
        this.$refs.cropper.replace(image);
        this.stopCropping(); // return to start mode
        // URL.revokeObjectURL(image) // remove image from memory �
      }, this.imgType);
    },
    // image editing
    toDash: (str) => str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase(),
    /** draw a new image with the filter effects embedded and replace the uploaded one with it */
    addEffect() {
      this.filteringLoader = true;

      const canvas = this.$refs.cropper.getCroppedCanvas(); // get the canvas
      const ctx = canvas.getContext("2d"); // apply 2d context

      // apply the css filters on the context
      ctx.filter = `grayscale(${this.filterObject.grayscale}) sepia(${this.filterObject.sepia}) saturate(${this.filterObject.saturate}) invert(${this.filterObject.invert}) brightness(${this.filterObject.brightness}) contrast(${this.filterObject.contrast}) hue-rotate(${this.filterObject.hueRotate}deg) blur(${this.filterObject.blur}px)`;
      ctx.drawImage(this.$refs.img, 0, 0, canvas.width, canvas.height);

      // canvas to image file format
      // canvas.toBlob( blob => {

      //    let image = URL.createObjectURL(blob) // create a referable image url
      //    this.imgSrc = image // replace the preview img (filters) and the canvas image (cropper)
      //    this.startCropping() // get the cropper canvas onto the dom
      //    this.$refs.cropper.replace(image)
      //    this.stopCropping() // remove it from the dom
      //    this.filterObject = {
      //       grayscale: 0,
      //       sepia: 0,
      //       saturate: 1,
      //       hueRotate: 0,
      //       invert: 0,
      //       brightness: 1,
      //       contrast: 1,
      //       blur: 0,
      //    }

      //    // URL.revokeObjectURL(image) // remove image from memory �
      // }, this.imgType)
      this.imgSrc = canvas.toDataURL(this.imgMimeType, 1); // replace the preview img (filters) and the canvas image (cropper)
      this.startCropping(); // get the cropper canvas onto the dom
      this.$refs.cropper.replace(this.imgSrc);
      this.stopCropping(); // remove it from the dom
      this.filteringLoader = false;
    },
    download() {
      const link = document.createElement("a");
      link.href = this.imgSrc;

      link.download =
        this.imgName.replace(/.jpeg|.png|.svg|.jpg/gi, "") +
        "-edited-" +
        now() +
        "." +
        (this.imgType == "jpeg" ? "jpg" : this.imgType);

      link.click();
    },
    resetImage() {
      this.imgSrc = this.bkpImgSrc;
      this.startCropping();
      this.$refs.cropper.replace(this.bkpImgSrc);
      this.stopCropping();
    },
    openSaveAsDialog() {
      this.saveAsFileName = this.suggestedFileName;
      this.showSaveAsDialog = true;
      // Focus the input field after the modal renders
      this.$nextTick(() => {
        if (this.$refs.fileNameInput) {
          this.$refs.fileNameInput.focus();
          this.$refs.fileNameInput.select();
        }
      });
    },
    closeSaveAsDialog() {
      this.showSaveAsDialog = false;
      this.saveAsFileName = "";
    },
    async saveAsFile() {
      if (!this.saveAsFileName.trim()) {
        return;
      }

      try {
        // Convert the current image to a blob
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        const img = new Image();

        img.onload = async () => {
          canvas.width = img.width;
          canvas.height = img.height;
          ctx.drawImage(img, 0, 0);

          canvas.toBlob(
            async (blob) => {
              // Create a File object from the blob
              const file = new File([blob], this.saveAsFileName, {
                type: this.imgMimeType,
              });

              // Upload the file using the existing upload functionality
              await this.$store.dispatch("fm/content/upload", {
                files: [file],
                overwrite: false,
              });

              this.handleSuccess(this.$t("File saved successfully"));
              this.closeSaveAsDialog();

              // Refresh the file list
              await this.$store.dispatch("fm/content/loadContent");
            },
            this.imgMimeType,
            1.0,
          );
        };

        img.src = this.imgSrc;
      } catch (error) {
        console.error("Error saving file:", error);
        this.handleError(this.$t("Error saving file"));
      }
    },
    // getBackgroundColor() {
    //    let v = new Vibrant(this.imgSrc);
    //    v.getPalette().then(palette => {
    //       this.bgColor = palette.DarkMuted.hex;
    //    });
    // }
  },
};
</script>

<style lang="scss">
@media screen and (-webkit-min-device-pixel-ratio: 0) {
  input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    cursor: ew-resize;
    box-shadow: -405px 0 0 400px #0038ff;
    @apply h-3 w-3 rounded-full bg-yellow-500;
  }
}

.preview {
  width: 12rem !important;
  height: auto !important;

  img {
    width: 100% !important;
    height: 100% !important;
  }
}
/* cropperJS css */
.cropper-container {
  direction: ltr;
  font-size: 0;
  line-height: 0;
  position: relative;
  -ms-touch-action: none;
  touch-action: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.cropper-container img {
  display: block;
  height: 100%;
  image-orientation: 0deg;
  max-height: none !important;
  max-width: none !important;
  min-height: 0 !important;
  min-width: 0 !important;
  width: 100%;
}

.cropper-wrap-box,
.cropper-canvas,
.cropper-drag-box,
.cropper-crop-box,
.cropper-modal {
  bottom: 0;
  left: 0;
  position: absolute;
  right: 0;
  top: 0;
}

.cropper-wrap-box,
.cropper-canvas {
  overflow: hidden;
}

.cropper-drag-box {
  background-color: #fff;
  opacity: 0;
}

.cropper-modal {
  background-color: #000;
  opacity: 0.5;
}

.cropper-view-box {
  display: block;
  height: 100%;
  /* @apply border border-theme-500;  */
  outline: 1px solid #39f;
  outline-color: rgba(51, 153, 255, 0.75);
  overflow: hidden;
  width: 100%;
}

.cropper-dashed {
  border: 0 dashed #eee;
  display: block;
  opacity: 0.5;
  position: absolute;
}

.cropper-dashed.dashed-h {
  border-bottom-width: 1px;
  border-top-width: 1px;
  height: calc(100% / 3);
  left: 0;
  top: calc(100% / 3);
  width: 100%;
}

.cropper-dashed.dashed-v {
  border-left-width: 1px;
  border-right-width: 1px;
  height: 100%;
  left: calc(100% / 3);
  top: 0;
  width: calc(100% / 3);
}

.cropper-center {
  display: block;
  height: 0;
  left: 50%;
  opacity: 0.75;
  position: absolute;
  top: 50%;
  width: 0;
}

.cropper-center::before,
.cropper-center::after {
  background-color: #eee;
  content: " ";
  display: block;
  position: absolute;
}

.cropper-center::before {
  height: 1px;
  left: -3px;
  top: 0;
  width: 7px;
}

.cropper-center::after {
  height: 7px;
  left: 0;
  top: -3px;
  width: 1px;
}

.cropper-face,
.cropper-line,
.cropper-point {
  display: block;
  height: 100%;
  opacity: 0.1;
  position: absolute;
  width: 100%;
}

.cropper-face {
  background-color: #fff;
  left: 0;
  top: 0;
}

.cropper-line {
  background-color: #39f;
}

.cropper-line.line-e {
  cursor: ew-resize;
  right: -3px;
  top: 0;
  width: 5px;
}

.cropper-line.line-n {
  cursor: ns-resize;
  height: 5px;
  left: 0;
  top: -3px;
}

.cropper-line.line-w {
  cursor: ew-resize;
  left: -3px;
  top: 0;
  width: 5px;
}

.cropper-line.line-s {
  bottom: -3px;
  cursor: ns-resize;
  height: 5px;
  left: 0;
}

.cropper-point {
  background-color: #39f;
  height: 5px;
  opacity: 0.75;
  width: 5px;
}

.cropper-point.point-e {
  cursor: ew-resize;
  margin-top: -3px;
  right: -3px;
  top: 50%;
}

.cropper-point.point-n {
  cursor: ns-resize;
  left: 50%;
  margin-left: -3px;
  top: -3px;
}

.cropper-point.point-w {
  cursor: ew-resize;
  left: -3px;
  margin-top: -3px;
  top: 50%;
}

.cropper-point.point-s {
  bottom: -3px;
  cursor: s-resize;
  left: 50%;
  margin-left: -3px;
}

.cropper-point.point-ne {
  cursor: nesw-resize;
  right: -3px;
  top: -3px;
}

.cropper-point.point-nw {
  cursor: nwse-resize;
  left: -3px;
  top: -3px;
}

.cropper-point.point-sw {
  bottom: -3px;
  cursor: nesw-resize;
  left: -3px;
}

.cropper-point.point-se {
  bottom: -3px;
  cursor: nwse-resize;
  height: 20px;
  opacity: 1;
  right: -3px;
  width: 20px;
}

@media (min-width: 768px) {
  .cropper-point.point-se {
    height: 15px;
    width: 15px;
  }
}

@media (min-width: 992px) {
  .cropper-point.point-se {
    height: 10px;
    width: 10px;
  }
}

@media (min-width: 1200px) {
  .cropper-point.point-se {
    height: 5px;
    opacity: 0.75;
    width: 5px;
  }
}

.cropper-point.point-se::before {
  background-color: #39f;
  bottom: -50%;
  content: " ";
  display: block;
  height: 200%;
  opacity: 0;
  position: absolute;
  right: -50%;
  width: 200%;
}

.cropper-invisible {
  opacity: 0;
}

.cropper-bg {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC");
}

.cropper-hide {
  display: block;
  height: 0;
  position: absolute;
  width: 0;
}

.cropper-hidden {
  display: none !important;
}

.cropper-move {
  cursor: move;
}

.cropper-crop {
  cursor: crosshair;
}

.cropper-disabled .cropper-drag-box,
.cropper-disabled .cropper-face,
.cropper-disabled .cropper-line,
.cropper-disabled .cropper-point {
  cursor: not-allowed;
}
</style>
