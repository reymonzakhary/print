<template>
  <div class="w-screen">
    <SidePanel width="w-3/4" full-height="true" @on-close="close">
      <template #side-panel-header>
        <h2 class="bg-gray-100 p-2 text-sm font-bold uppercase tracking-wide">
          Select your file(s)
        </h2>
        <Teleport to="body">
          <transition name="fade">
            <component :is="modalName" />
          </transition>
        </Teleport>
      </template>

      <template #side-panel-content>
        <ContextMenu />
        <PasteMenu v-if="$store.state.fm.filemanager.clipboard.files.length" />

        <div class="flex h-dvh justify-between bg-gray-100 p-4">
          <section class="h-full w-1/6 overflow-auto" style="max-height: calc(100vh - 8rem)">
            <span
              class="cursor-pointer capitalize hover:text-theme-500"
              @click="selectDirectory('/')"
            >
              <font-awesome-icon :icon="['fas', 'hard-drive']" class="mr-1 text-theme-500" />
              {{ selectedDisk }}
            </span>
            <!-- <keep-alive> -->
            <Branch :parent-id="0" class="-ml-3" />
          </section>

          <!-- content-->
          <section class="h-full w-full lg:w-5/6 xl:w-4/6">
            <section class="mb-4 flex w-full items-center justify-between text-sm">
              <div>
                <font-awesome-icon
                  :icon="['fal', 'hard-drive']"
                  class="mr-1 cursor-pointer text-theme-500 hover:text-theme-300"
                  @click="selectDirectory('/')"
                />

                <span
                  v-for="(item, index) in breadcrumb"
                  :key="index"
                  class="cursor-pointer hover:text-theme-500"
                  :class="[breadcrumb.length === index + 1 ? 'font-bold' : '']"
                  @click="selectDirectory(item.path)"
                >
                  <span v-if="item.label" class="text-xs"> / </span>
                  {{ item.label }}
                </span>
              </div>

              <div class="flex divide-x">
                <button
                  class="flex items-center px-2 text-theme-500"
                  @click="showModal('NewFolder')"
                >
                  <font-awesome-icon :icon="['fad', 'folder-plus']" class="mr-1" />
                  {{ $t("create directory") }}
                </button>
                <button class="flex items-center px-2 text-theme-500" @click="showModal('NewFile')">
                  <font-awesome-icon :icon="['fad', 'file-plus']" class="mr-1" />
                  {{ $t("create file") }}
                </button>

                <button class="flex items-center px-2 text-theme-500" @click="showModal('Upload')">
                  <font-awesome-icon :icon="['fad', 'file-arrow-up']" class="mr-1" />
                  {{ $t("upload file") }}
                </button>
              </div>
            </section>

            <section>
              <!--folders component -->
              <!-- <label
								class="flex items-center text-xs font-bold tracking-wide uppercase"
							>
								<div class="select-none">{{ $t("folder") }}</div>
							</label>

							<Folders :parent-id="0" /> -->

              <template
                v-if="
                  disks &&
                  disks.length > 1 &&
                  (!breadcrumb || (breadcrumb.length > 1 && breadcrumb[1].path === '/')) &&
                  diskSelector
                "
              >
                <div class="select-none text-base font-bold uppercase tracking-wide">
                  {{ $t("disks") }}
                </div>
                <DiskList />
              </template>

              <div class="relative">
                <!-- search -->
                <FilemanagerFileSearch />
              </div>

              <!-- files component -->
              <div class="mt-12 flex items-center justify-between text-xs font-bold uppercase">
                <div class="select-none">{{ $t("files") }}</div>
                <div class="text-md mx-4 flex">
                  <button
                    class="focus:shadow-outline mx-0 rounded-l-sm px-3 py-1 text-white hover:bg-theme-300 focus:outline-none"
                    :class="
                      $store.state.fm.filemanager.filesView === 'list'
                        ? 'bg-theme-300'
                        : 'bg-theme-400'
                    "
                    @click="$store.commit('fm/filemanager/setView', 'list')"
                  >
                    <font-awesome-icon :icon="['fas', 'list-ul']" />
                  </button>

                  <button
                    :class="
                      $store.state.fm.filemanager.filesView === 'grid'
                        ? 'bg-theme-300'
                        : 'bg-theme-400'
                    "
                    class="focus:shadow-outline mx-0 rounded-r-sm px-3 py-1 text-white hover:bg-theme-200 focus:outline-none"
                    @click="$store.commit('fm/filemanager/setView', 'grid')"
                  >
                    <font-awesome-icon :icon="['fas', 'table-cells']" />
                  </button>
                </div>
              </div>

              <Files :selecting="true" :multiple="multiple" />
            </section>
          </section>

          <section class="flex w-2/6 flex-col bg-white pl-4">
            <template v-if="!multiple">
              <div
                v-if="
                  selectedItem && selectedItem.type === 'file' && thisImage(selectedItem.extension)
                "
                class="my-1 max-w-[300px] rounded bg-gray-200 italic text-gray-600"
              >
                <DetailsImage :disk="selectedDisk" :file="selectedItem" :size="300" />
                <div class="p-2">
                  {{ selectedItem.path }}
                </div>
              </div>
              <div
                v-if="
                  selectedItem &&
                  !thisImage(selectedItem.extension) &&
                  !thisVideo(selectedItem.extension) &&
                  selectedItem.type === 'file'
                "
                class="w-full text-center italic text-gray-500"
              >
                <font-awesome-icon
                  :icon="['fal', extensionToIcon(selectedItem.extension)]"
                  class="fa-7x mx-auto text-center"
                />
                <div class="p-2">{{ selectedItem.path }}</div>
              </div>
              <div
                v-if="selectedItem && selectedItem.type === 'dir'"
                class="w-full text-center italic text-gray-500"
              >
                <font-awesome-icon :icon="['fal', 'folder']" class="fa-7x mx-auto text-center" />
                <div class="p-2">{{ selectedItem.path }}</div>
              </div>
            </template>

            <template v-else>
              <div v-for="item in selectedItems" :key="`file_${item.filename}`">
                <div
                  v-if="item && item.type === 'file'"
                  class="my-1 flex items-center justify-between rounded bg-gray-200 p-2 italic text-gray-600"
                >
                  <Thumbnail :disk="selectedDisk" :file="item" />
                  {{ item.filename }}
                </div>
                <div
                  v-else-if="item && item.type === 'dir'"
                  class="flex justify-center text-gray-500"
                >
                  <font-awesome-icon
                    :icon="['fal', extensionToIcon(item.extension)]"
                    class="fa-7x mx-auto text-center"
                  />
                  <div class="p-2">{{ item.path }}</div>
                </div>
              </div>
            </template>
            <button
              v-if="selectedItem"
              class="mx-auto mt-4 w-full self-center rounded-full bg-green-500 px-2 py-1 text-white transition-colors hover:bg-green-600 md:w-1/2"
              @click="
                ($emit('updateValue', { path: selectedItem.path }),
                $emit('updateValues', selectedItems),
                close(),
                $emit('updatesSetting', selectedItem))
              "
            >
              {{ $t("Add file(s)") }}
            </button>
          </section>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
// modal views
import NewFile from "~/components/filemanager/views/NewFile.vue";
import NewFolder from "~/components/filemanager/views/NewFolder.vue";
import Upload from "~/components/filemanager/views/FileUpload.vue";
import Delete from "~/components/filemanager/views/Delete.vue";
import Clipboard from "~/components/filemanager/views/Clipboard.vue";
import Status from "~/components/filemanager/views/Status.vue";
import Rename from "~/components/filemanager/views/Rename.vue";
import Properties from "~/components/filemanager/views/Properties.vue";
import Preview from "~/components/filemanager/views/Preview.vue";
import TextEdit from "~/components/filemanager/views/TextEdit.vue";
import AudioPlayer from "~/components/filemanager/views/AudioPlayer.vue";
import PDFViewer from "~/components/filemanager/views/PDFViewer.vue";
import VideoPlayer from "~/components/filemanager/views/VideoPlayer.vue";
import Zip from "~/components/filemanager/views/Zip.vue";
import Unzip from "~/components/filemanager/views/Unzip.vue";
import PreviewLoader from "~/components/filemanager/views/PreviewLoader.vue";

import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  components: {
    NewFile,
    NewFolder,
    Upload,
    Delete,
    Clipboard,
    Status,
    Rename,
    Properties,
    Preview,
    TextEdit,
    AudioPlayer,
    PDFViewer,
    VideoPlayer,
    Zip,
    Unzip,
    PreviewLoader,
  },
  mixins: [managerhelper, helper],
  props: {
    multiple: {
      type: Boolean,
      default: false,
    },
    disk: {
      required: false,
      type: String,
      default: "tenancy",
    },
    diskSelector: {
      required: false,
      type: Boolean,
    },
  },
  fetchOnServer: true,
  emits: ["updateValue", "updatesSetting", "parentModelData", "updateValues"],
  data() {
    return {
      enabled: false,
      upload: false,
      // disk: null,
      initLoading: true,
    };
  },
  async fetch() {
    if (this.$store.state.fm.content.selectedDisk === null) {
      await this.$store.dispatch("fm/filemanager/initializeApp");
    }
  },
  computed: {
    ...mapState({
      filesizes: (state) => state.fm.content.sizes,
      selectedDisk: (state) => state.fm.content.selectedDisk,
      sortSettings: (state) => state.fm.content.sort,
      modalName: (state) => state.fm.modal.modalName,
      imageExtensions: (state) => state.fm.settings.imageExtensions,
      videoExtensions: (state) => state.fm.settings.videoExtensions,
    }),
    ...mapGetters({
      disks: `fm/filemanager/diskList`,
      breadcrumb: `fm/content/breadcrumb`,
      directories: `fm/content/directories`,
      files: `fm/content/files`,
      selectedItems: `fm/content/selectedList`,
    }),

    selectedItem() {
      return this.selectedItems[0];
    },

    content() {
      return this.files.length + this.directories.length;
    },
  },
  watch: {
    files() {
      if (this.files.length > 0) {
        this.$store.commit("fm/content/toggleLoading", false);
      }
    },
    directories() {
      if (this.directories.length > 0) {
        this.$store.commit("fm/content/toggleLoading", false);
      }
    },
  },
  async created() {
    this.$nextTick(() => {
      if (this.disk) {
        // set disk from parent prop
        this.selectDisk({ disk: this.disk });
      }

      this.toggleViewFolders(true); // show folders by default

      // } else {
      // 	this.selectDisk({ disk: "tenancy" });
      // }
    });
  },
  // mounted() {
  // 	this.disk = this.selectedDisk; // replaced with static disk to enforce upload directory
  // },
  beforeUpdate() {
    // if disk changed
    // if (this.disk !== this.selectedDisk) {
    // 	this.disk = this.selectedDisk;
    // }
  },
  methods: {
    ...mapActions({
      selectDisk: "fm/filemanager/selectDisk",
    }),
    ...mapMutations({
      toggleViewFolders: "fm/filemanager/toggleViewFolders",
    }),
    close() {
      this.$parent.showFileManagerSelectPanel = false;
      this.$emit("parentModelData", false);
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
    sum(obj) {
      let sum = 0;
      for (const el in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, el)) {
          sum += parseFloat(obj[el]);
        }
      }
      return sum;
    },
    doneSorting() {
      // console.log("done sorting!");
    },
    showModal(modalName) {
      // show selected modal
      this.$store.commit("fm/modal/setModalState", {
        modalName,
        show: true,
      });
    },

    sortBy(field) {
      this.$store.dispatch(`fm/content/sortBy`, { field, direction: null });
    },

    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
