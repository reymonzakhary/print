<template>
  <div>
    <div
      class="fixed bottom-0 left-0 top-0 z-50 flex h-full w-full items-center justify-center bg-black bg-opacity-90"
      @click="closeViewer"
    >
      <!-- Close button -->
      <button
        class="absolute right-0 top-0 z-10 m-4 flex items-center text-white transition-colors hover:text-gray-300"
        @click.stop="closeViewer"
      >
        {{ $t("close") }}
        <font-awesome-icon :icon="['fad', 'circle-xmark']" class="ml-2" />
      </button>

      <!-- Navigation hint - moved to top center -->
      <div
        v-if="imageFiles.length > 1"
        class="absolute left-1/2 top-4 z-10 -translate-x-1/2 transform rounded bg-black bg-opacity-50 p-3 text-center text-sm text-white"
      >
        <p class="mb-1">{{ $t("use arrow keys or click arrows to navigate") }}</p>
        <p class="text-xs text-gray-300">{{ $t("press escape to close") }}</p>
      </div>

      <!-- Previous button -->
      <button
        v-if="canNavigatePrevious"
        class="absolute left-4 top-1/2 z-10 -translate-y-1/2 transform rounded-full bg-black bg-opacity-70 p-4 text-white shadow-lg transition-all hover:scale-110 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 md:p-4"
        :title="$t('previous image')"
        aria-label="Previous image"
        @click.stop="navigatePrevious"
      >
        <font-awesome-icon :icon="['fad', 'chevron-left']" class="text-2xl md:text-3xl" />
      </button>

      <!-- Next button -->
      <button
        v-if="canNavigateNext"
        class="absolute right-4 top-1/2 z-10 -translate-y-1/2 transform rounded-full bg-black bg-opacity-70 p-4 text-white shadow-lg transition-all hover:scale-110 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 md:p-4"
        :title="$t('next image')"
        aria-label="Next image"
        @click.stop="navigateNext"
      >
        <font-awesome-icon :icon="['fad', 'chevron-right']" class="text-2xl md:text-3xl" />
      </button>

      <!-- Mobile navigation areas (larger touch targets) -->
      <div
        v-if="canNavigatePrevious"
        class="z-5 absolute left-0 top-0 h-full w-1/3 cursor-pointer md:hidden"
        :title="$t('previous image')"
        @click.stop="navigatePrevious"
      />
      <div
        v-if="canNavigateNext"
        class="z-5 absolute right-0 top-0 h-full w-1/3 cursor-pointer md:hidden"
        :title="$t('next image')"
        @click.stop="navigateNext"
      />

      <!-- Image container -->
      <div class="relative flex h-full w-full items-center justify-center p-8" @click.stop>
        <img
          v-if="currentImageSrc"
          :src="currentImageSrc"
          :alt="currentFile?.basename || 'Image'"
          class="max-h-full max-w-full object-contain transition-opacity duration-300"
          :class="{ 'opacity-0': !imageLoaded, 'opacity-100': imageLoaded }"
          @load="imageLoaded = true"
          @error="imageError = true"
        />

        <!-- Loading indicator -->
        <div v-if="!currentImageSrc && !imageError" class="text-center text-white">
          <font-awesome-icon :icon="['fad', 'atom']" class="mb-4 animate-spin text-6xl" />
          <p class="text-lg">{{ $t("loading image") }}...</p>
          <p class="mt-2 text-sm text-gray-300">{{ currentFile?.basename }}</p>
        </div>

        <!-- Error indicator -->
        <div v-else-if="imageError" class="text-center text-white">
          <font-awesome-icon
            :icon="['fad', 'exclamation-triangle']"
            class="mb-4 text-4xl text-red-400"
          />
          <p class="mb-2">{{ $t("failed to load image") }}</p>
          <p class="text-sm text-gray-400">{{ currentFile?.basename || "Unknown file" }}</p>
        </div>

        <!-- No images found -->
        <div v-else-if="imageFiles.length === 0" class="text-center text-white">
          <font-awesome-icon :icon="['fad', 'images']" class="mb-4 text-4xl text-gray-400" />
          <p>{{ $t("no images found") }}</p>
        </div>
      </div>

      <!-- Image info overlay -->
      <div
        v-if="currentFile && imageLoaded"
        class="absolute bottom-4 left-4 rounded bg-black bg-opacity-50 p-3 text-white"
      >
        <p class="font-semibold">{{ currentFile.basename }}</p>
        <p class="text-sm text-gray-300">
          {{ formatBytes(currentFile.size) }} • {{ currentImageIndex + 1 }} /
          {{ imageFiles.length }}
        </p>
      </div>

      <!-- Action buttons - moved to bottom right -->
      <div class="absolute bottom-4 right-4 z-10 flex space-x-2">
        <!-- Download button -->
        <button
          class="flex items-center rounded-md bg-gray-600 px-4 py-2 text-white shadow-lg transition-all hover:bg-gray-700 hover:shadow-xl"
          :title="$t('download')"
          @click.stop="downloadImage"
        >
          <font-awesome-icon :icon="['fad', 'download']" class="mr-2" />
          {{ $t("download") }}
        </button>

        <!-- Edit button -->
        <button
          v-if="permissions.includes('media-sources-update') && !(disk && file && file.path)"
          class="flex items-center rounded-md bg-theme-500 px-4 py-2 text-white shadow-lg transition-all hover:bg-theme-600 hover:shadow-xl"
          :title="$t('edit')"
          @click.stop="openEditor"
        >
          <font-awesome-icon :icon="['fad', 'edit']" class="mr-2" />
          {{ $t("edit") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Converted to <script setup> syntax. Comments/documentation will be added next.

import { computed, ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useStore } from "vuex";

const props = defineProps({
  disk: { type: String, required: false, default: null },
  file: { type: Object, required: false, default: null },
  allFiles: { type: Array, required: false, default: () => [] },
});

const emit = defineEmits(["close"]);

const store = useStore();
const { permissions } = storeToRefs(useAuthStore());
const api = useAPI();

const currentImageIndex = ref(0);
const currentImageSrc = ref(null);
const imageLoaded = ref(false);
const imageError = ref(false);

const files = computed(() => store.getters["fm/content/files"]);
const selectedFiles = computed(() => store.getters["fm/content/selectedFiles"]);
const selectedDisk = computed(() => store.getters["fm/content/selectedDisk"]);
const selectedItems = computed(() => store.getters["fm/content/selectedList"]);
const imageExtensions = computed(() => store.state.fm.settings.imageExtensions);

const imageFiles = computed(() => {
  if (props.file && props.file.path) {
    if (
      !props.file.extension ||
      imageExtensions.value.includes(props.file.extension.toLowerCase())
    ) {
      return [props.file];
    }
  }
  if (props.allFiles.length === 0 && selectedFiles.value && selectedFiles.value.length > 0) {
    const localSelectedFiles = selectedFiles.value.map((file) => {
      return {
        path: file,
      };
    });
    return localSelectedFiles;
  }
  if (props.allFiles.length === 0 && files.value && files.value.length > 0) {
    return files.value.filter(
      (file) => file.extension && imageExtensions.value.includes(file.extension.toLowerCase()),
    );
  }
  return props.allFiles;
});

const currentFile = computed(() => {
  return imageFiles.value[currentImageIndex.value] || null;
});

const canNavigatePrevious = computed(() => {
  return imageFiles.value.length > 1 && currentImageIndex.value > 0;
});
const canNavigateNext = computed(() => {
  return imageFiles.value.length > 1 && currentImageIndex.value < imageFiles.value.length - 1;
});

async function loadCurrentImage() {
  if (!currentFile.value) return;
  imageLoaded.value = false;
  imageError.value = false;
  if (currentImageSrc.value && currentImageSrc.value.startsWith("data:")) {
    currentImageSrc.value = null;
  }
  try {
    const disk = props.disk || selectedDisk.value;
    let fileObj = currentFile.value;
    if (fileObj && (!fileObj.extension || !fileObj.basename || !fileObj.size)) {
      try {
        const infoUrl = `media-manager/file-manager/content/search?disk=${disk}&name=${fileObj.path.split("/").pop()}`;
        const infoResp = await api.get(infoUrl);
        if (infoResp && typeof infoResp === "object") {
          fileObj = { ...fileObj, ...infoResp };
          // Not reactive, but good enough for preview
        }
        currentFile.value.basename = infoResp.data[0]?.basename;
        currentFile.value.size = infoResp.data[0]?.size;
      } catch (e) {}
    }
    const url = `media-manager/file-manager/preview?disk=${disk}&path=${fileObj.path}`;
    const response = await api.get(url, { responseType: "arrayBuffer" });
    if (response instanceof ArrayBuffer && response.byteLength > 0) {
      const mimeType = getMimeTypeFromArrayBuffer(response);
      const imgBase64 = arrayBufferToBase64(response);
      currentImageSrc.value = `data:${mimeType};base64,${imgBase64}`;
    } else {
      throw new Error("Invalid response format or empty data");
    }
  } catch (error) {
    imageError.value = true;
    currentImageSrc.value = null;
  }
}

function navigatePrevious() {
  if (canNavigatePrevious.value) {
    currentImageIndex.value--;
  }
}
function navigateNext() {
  if (canNavigateNext.value) {
    currentImageIndex.value++;
  }
}
function handleKeyDown(event) {
  switch (event.key) {
    case "ArrowLeft":
      event.preventDefault();
      navigatePrevious();
      break;
    case "ArrowRight":
      event.preventDefault();
      navigateNext();
      break;
    case "Escape":
      event.preventDefault();
      closeViewer();
      break;
  }
}
function closeViewer() {
  if (currentImageSrc.value && currentImageSrc.value.startsWith("data:")) {
    currentImageSrc.value = null;
  }
  document.removeEventListener("keydown", handleKeyDown);
  if (props.disk && ((props.file && props.file.path) || props.allFiles.length > 0)) {
    emit("close");
  } else {
    store.commit("fm/modal/clearModal");
  }
}
async function openEditor() {
  // if (currentFile.value) {
  //   store.commit("fm/content/resetSelected");
  //   store.commit("fm/content/setSelected", {
  //     type: "files",
  //     path: currentFile.value.path,
  //   });
  //   await Promise.resolve();
  // }
  store.commit("fm/modal/setModalState", {
    modalName: "Preview",
    show: true,
  });
}
function formatBytes(bytes, decimals = 2) {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}
function downloadImage() {
  if (!currentImageSrc.value || !currentFile.value) return;
  const link = document.createElement("a");
  link.href = currentImageSrc.value;
  link.download = currentFile.value.basename;
  link.click();
}
function arrayBufferToBase64(buffer) {
  let binary = "";
  const bytes = new Uint8Array(buffer);
  const len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}
function getMimeTypeFromArrayBuffer(arrayBuffer) {
  const uint8arr = new Uint8Array(arrayBuffer);
  const len = 4;
  if (uint8arr.length >= len) {
    const signatureArr = new Array(len);
    for (let i = 0; i < len; i++) {
      signatureArr[i] = uint8arr[i].toString(16).padStart(2, "0").toUpperCase();
    }
    const signature = signatureArr.join("");
    switch (signature) {
      case "89504E47":
        return "image/png";
      case "47494638":
        return "image/gif";
      case "FFD8FFDB":
      case "FFD8FFE0":
      case "FFD8FFE1":
      case "FFD8FFE2":
      case "FFD8FFE3":
      case "FFD8FFE8":
        return "image/jpeg";
      case "52494646":
        return "image/webp";
      default:
        return "image/jpeg";
    }
  }
  return "image/jpeg";
}

onMounted(async () => {
  // If file prop is provided, don't change currentImageIndex
  if (!(props.file && props.file.path)) {
    if (selectedItems.value.length > 0) {
      const selectedFile = selectedItems.value[0];
      const selectedIndex = imageFiles.value.findIndex((file) => file.path === selectedFile.path);
      if (selectedIndex !== -1) {
        currentImageIndex.value = selectedIndex;
      } else {
        const selectedIndexByName = imageFiles.value.findIndex(
          (file) => file.basename === selectedFile.basename,
        );
        if (selectedIndexByName !== -1) {
          currentImageIndex.value = selectedIndexByName;
        }
      }
    } else {
      if (imageFiles.value.length > 0) {
        currentImageIndex.value = 0;
      }
    }
  }
  if (imageFiles.value.length === 0) {
    imageError.value = true;
    return;
  }
  await loadCurrentImage();
  document.addEventListener("keydown", handleKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener("keydown", handleKeyDown);
  if (currentImageSrc.value && currentImageSrc.value.startsWith("data:")) {
    currentImageSrc.value = null;
  }
});

watch(currentImageIndex, () => {
  loadCurrentImage();
});
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
