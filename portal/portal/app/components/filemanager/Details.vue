<template>
  <div>
    <transition name="slide">
      <div v-if="selectedItem" class="h-full overflow-y-auto text-sm">
        <DetailsImage
          v-if="
            selectedItem &&
            selectedItem.type === 'file' &&
            thisImage(selectedItem.extension) &&
            selectedItem.extension !== 'pdf'
          "
          :disk="selectedDisk"
          :file="selectedItem"
          :size="250"
          class="shadow-sm"
        >
        </DetailsImage>

        <PDFThumbnail
          v-if="
            selectedItem &&
            selectedItem.type === 'file' &&
            selectedItem.extension === 'pdf'
          "
          :disk="selectedDisk"
          :file="selectedItem"
          class="object-contain"
        />

        <div
          v-if="
            !thisImage(selectedItem.extension) &&
            !thisVideo(selectedItem.extension) &&
            selectedItem.extension !== 'pdf' &&
            selectedItem.type === 'file'
          "
          class="py-4 mt-4 text-center"
        >
          <font-awesome-icon
            :icon="['fal', extensionToIcon(selectedItem.extension)]"
            class="mx-auto text-center text-white fa-7x"
          />
        </div>

        <VideoPreview
          v-if="thisVideo(selectedItem.extension) && refreshVideo"
        />

        <div v-if="selectedItem.type === 'dir'" class="w-full py-4 text-center">
          <font-awesome-icon
            :icon="['fas', 'folder']"
            class="text-white fa-7x"
          />
        </div>

        <section class="p-4">
          <p
            v-tooltip.left="selectedItem.basename"
            class="mb-4 font-bold truncate text-md"
            @click="copyToClipboard(selectedItem.basename)"
          >
            {{ selectedItem.basename }}
          </p>

          <section class="leading-loose">
            <p class="text-xs font-bold tracking-wide uppercase">
              {{ $t("details") }}
            </p>

            <div class="flex flex-wrap justify-between">
              <div class="">{{ $t("disk") }}</div>
              <div class="font-bold" @click="copyToClipboard(selectedDisk)">
                {{ selectedDisk }}
              </div>
            </div>

            <div class="flex flex-wrap justify-between">
              <div class="">Path</div>
              <div
                v-tooltip.left="selectedItem.path"
                class="w-1/2 font-bold truncate"
              >
                {{ selectedItem.path }}
              </div>
            </div>

            <template v-if="selectedItem.type === 'file'">
              <div class="flex flex-wrap justify-between">
                <div class="">{{ $t("size") }}</div>
                <div class="font-bold">
                  {{ bytesToHuman(selectedItem.size) }}
                </div>
              </div>

              <!-- <div class="flex flex-wrap justify-between">
                  <div class="">Url</div>
                  <span v-if="url" class="font-bold">{{ url }}</span>
                  <span v-else>
                     <button @click="getUrl" type="button" class="px-1 text-xs bg-white rounded text-theme-500">
                        <i class="fas fa-sm fa-link"></i> Get URL
                     </button>
                  </span>
               </div> -->
            </template>

            <template v-if="selectedItem.hasOwnProperty('timestamp')">
              <div class="flex flex-wrap justify-between">
                <div class="">{{ $t("updated") }}</div>
                <div class="font-bold">
                  {{ timestampToDate(selectedItem.timestamp) }}
                </div>
              </div>
            </template>

            <template v-if="selectedItem.hasOwnProperty('acl')">
              <div class="flex flex-wrap justify-between">
                <div class="">Access</div>
                <div class="font-bold">
                  {{ "access_" + selectedItem.acl }}
                </div>
              </div>
            </template>
          </section>
        </section>
        <Tags
          :selected-tags="selectedItem.tags ? selectedItem.tags : []"
          class="p-2 m-2 bg-white rounded"
          @on-update-tags="attachTag"
        ></Tags>
      </div>

      <div v-else class="flex items-center justify-center h-full text-center">
        <p class="block my-auto text-theme-200">
          <font-awesome-icon
            :icon="['fad', 'person-dolly-empty']"
            class="block text-white fa-7x"
          />
          {{ $t("nothing selected") }}
        </p>
      </div>
    </transition>
  </div>
</template>

<script>
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import VideoPreview from "./views/VideoPreview.vue";

import { useStore } from "vuex";

export default {
  name: "Properties",
  components: { VideoPreview },
  mixins: [helper],
  setup() {
    const eventStore = useEventStore();
    const store = useStore();
    return { eventStore, store };
  },
  data() {
    return {
      url: null,
      refreshVideo: true,
    };
  },
  computed: {
    selectedDisk() {
      return this.store.getters["fm/content/selectedDisk"];
    },
    selectedItem() {
      return this.store.getters["fm/content/selectedList"][0];
    },
    imageExtensions() {
      return this.store.state.fm.settings.imageExtensions;
    },
    videoExtensions() {
      return this.store.state.fm.settings.videoExtensions;
    },
  },
  watch: {
    selectedItem() {
      if (this.thisVideo(this.selectedItem?.extension)) {
        this.refreshVideo = false;
        setTimeout(() => {
          this.refreshVideo = true;
        }, 10);
      }
    },
  },
  methods: {
    getUrl() {
      this.store
        .dispatch("fm/filemanager/url", {
          disk: this.selectedDisk,
          path: this.selectedItem.path,
        })
        .then((response) => {
          if (response.data.result.status === "success") {
            this.url = response.data.url;
          }
        });
    },
    copyToClipboard(text) {
      // create input
      const copyInputHelper = document.createElement("input");
      copyInputHelper.className = "copyInputHelper";
      document.body.appendChild(copyInputHelper);
      // add text
      copyInputHelper.value = text;
      copyInputHelper.select();
      // copy text to clipboard
      document.execCommand("copy");
      // clear
      document.body.removeChild(copyInputHelper);

      // Notification
      this.eventStore.emit("addNotification", {
        status: "success",
        message: "Copied to clipboard!",
      });
    },
    thisImage(extension) {
      if (this.selectedItem) {
        // extension not found
        if (!extension) return false;
        return this.imageExtensions.includes(extension.toLowerCase());
      }
    },
    thisVideo(extension) {
      if (this.selectedItem) {
        // extension not found
        if (!extension) return false;
        return this.videoExtensions.includes(extension.toLowerCase());
      }
    },
    closeModal() {
      this.store.commit("fm/modal/clearModal");
    },
    attachTag(tags) {
      this.store.dispatch("fm/content/attachTags", {
        path: this.selectedItem.path,
        tags: tags,
      });
    },
  },
};
</script>
