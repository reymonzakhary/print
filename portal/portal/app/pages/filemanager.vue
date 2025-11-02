<template>
  <div
    class="fm-body relative flex h-full flex-wrap content-start overflow-hidden p-4"
    @dragover="showModal('Upload')"
  >
    <transition name="fade">
      <component :is="modalName" :key="modalName" />
    </transition>

    <!-- header -->
    <section
      v-if="permissions.includes('media-sources-read')"
      class="mb-4 flex w-full flex-wrap items-center justify-center rounded bg-theme-400 px-2 py-1 text-themecontrast-400 md:flex-nowrap lg:h-10"
    >
      <transition name="fade">
        <!-- Filesizes -->
        <FMSizes v-if="filesizes?.general" :data="filesizes.general" :mode="1" />
      </transition>
    </section>

    <div class="relative flex h-full w-full content-start">
      <!-- folder tree -->
      <section class="mr-2 hidden w-full overflow-y-auto lg:block lg:w-1/6">
        <template v-if="permissions.includes('media-sources-list')">
          <span
            class="sticky top-0 mb-2 flex w-full cursor-pointer items-center bg-gray-100 py-1 capitalize shadow hover:text-theme-500 dark:bg-gray-800"
            @click="selectDirectory('/')"
          >
            <font-awesome-icon :icon="['fas', 'hard-drive']" class="mr-1 text-theme-500" />
            {{ selectedDisk }}
          </span>
          <Branch :parent-id="0" class="-ml-3 pb-24" />
        </template>
      </section>

      <!-- content-->
      <section class="overflow-none w-full lg:w-5/6 xl:w-4/6">
        <!-- Actions -->
        <section class="mb-4 flex w-full flex-wrap items-center justify-between text-sm">
          <!-- Breadcrumbs -->
          <div v-if="permissions.includes('media-sources-list')" class="w-full md:w-auto">
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

          <!-- buttons -->
          <div
            v-if="permissions.includes('media-sources-create')"
            class="mt-2 flex divide-x md:mt-0"
          >
            <button
              class="flex flex-wrap content-between items-center justify-center px-2 text-theme-500"
              @click="showModal('NewFolder')"
            >
              <font-awesome-icon
                :icon="['fad', 'folder-plus']"
                class="mx-auto w-full text-4xl sm:mr-1 sm:w-auto sm:text-base"
              />
              {{ $t("create directory") }}
            </button>
            <button
              class="flex flex-wrap items-center justify-center px-2 text-theme-500"
              @click="showModal('NewFile')"
            >
              <font-awesome-icon
                :icon="['fad', 'file-plus']"
                class="mx-auto w-full text-4xl sm:mr-1 sm:w-auto sm:text-base"
              />
              {{ $t("create file") }}
            </button>
            <button
              class="flex flex-wrap items-center justify-center px-2 text-theme-500"
              @click="showModal('Upload')"
            >
              <font-awesome-icon
                :icon="['fad', 'file-arrow-up']"
                class="mx-auto w-full text-4xl sm:mr-1 sm:w-auto sm:text-base"
              />
              {{ $t("upload file") }}
            </button>
          </div>
        </section>

        <section v-if="permissions.includes('media-sources-list')">
          <div class="relative">
            <!-- Disk list -->
            <template
              v-if="
                disks &&
                disks.length > 1 &&
                (!breadcrumb || (breadcrumb.length > 1 && breadcrumb[1].path === '/'))
              "
            >
              <div class="select-none text-base font-bold uppercase tracking-wide">
                {{ $t("disks") }}
              </div>
              <DiskList />
            </template>

            <!-- files & directories lisfocussed: false,t -->
            <div class="mt-6 flex items-center justify-between text-xs font-bold uppercase">
              <div class="invisible select-none text-base tracking-wide md:visible">
                {{ store.state.fm.filemanager.filesView === "list" ? $t("list") : $t("grid") }}
              </div>

              <!-- search -->
              <FilemanagerFileSearch />

              <!-- view toggle -->
              <div class="text-md ml-4 flex xl:mr-4">
                <client-only>
                  <ValueSwitch
                    icon="fa-folder"
                    :name="$t('show folders')"
                    :display-name="false"
                    :set-checked="store.state.fm.filemanager.viewFolders"
                    @checked-value="
                      (store.commit('fm/filemanager/toggleViewFolders', $event.value),
                      saveView($event.value, 'viewFolders'))
                    "
                  />
                </client-only>

                <button
                  class="focus:shadow-outline mx-0 rounded-l-sm px-3 py-1 text-white hover:bg-theme-300 focus:outline-none"
                  :class="
                    store.state.fm.filemanager.filesView === 'list'
                      ? 'bg-theme-300'
                      : 'bg-theme-400'
                  "
                  @click="
                    (store.commit('fm/filemanager/setView', 'list'), saveView('list', 'view'))
                  "
                >
                  <font-awesome-icon :icon="['fas', 'list-ul']" />
                </button>
                <button
                  :class="
                    store.state.fm.filemanager.filesView === 'grid'
                      ? 'bg-theme-300'
                      : 'bg-theme-400'
                  "
                  class="focus:shadow-outline mx-0 rounded-r-sm px-3 py-1 text-white hover:bg-theme-200 focus:outline-none"
                  @click="
                    (store.commit('fm/filemanager/setView', 'grid'), saveView('grid', 'view'))
                  "
                >
                  <font-awesome-icon :icon="['fas', 'table-cells']" />
                </button>
              </div>
            </div>

            <Files :multiple="true" />
          </div>
        </section>
      </section>

      <section
        class="hidden h-full w-1/6 rounded bg-theme-400 text-themecontrast-400 xl:block"
        style="max-height: calc(100% - 4rem)"
      >
        <!-- file details component -->
        <Details
          v-if="(files || folders) && permissions.includes('media-sources-read')"
          class="h-full"
        />
      </section>
    </div>

    <ContextMenu class="z-50" />
    <PasteMenu v-if="store.state.fm.filemanager.clipboard.files.length" />
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
import ImageViewer from "~/components/filemanager/views/ImageViewer.vue";
import TextEdit from "~/components/filemanager/views/TextEdit.vue";
import AudioPlayer from "~/components/filemanager/views/AudioPlayer.vue";
import PDFViewer from "~/components/filemanager/views/PDFViewer.vue";
import VideoPlayer from "~/components/filemanager/views/VideoPlayer.vue";
import Zip from "~/components/filemanager/views/Zip.vue";
import Unzip from "~/components/filemanager/views/Unzip.vue";
import PreviewLoader from "~/components/filemanager/views/PreviewLoader.vue";

import { mapState, mapGetters, useStore } from "vuex";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import FilemanagerFileSearch from "../components/filemanager/FilemanagerFileSearch.vue";

export default {
  components: {
    // modal views
    NewFile,
    NewFolder,
    Upload,
    Delete,
    Clipboard,
    Status,
    Rename,
    Properties,
    Preview,
    ImageViewer,
    TextEdit,
    AudioPlayer,
    PDFViewer,
    VideoPlayer,
    Zip,
    Unzip,
    FilemanagerFileSearch,
    PreviewLoader,
  },
  mixins: [managerhelper, helper],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const store = useStore();
    const eventStore = useEventStore();
    return { api, store, permissions, eventStore };
  },
  data() {
    return {
      enabled: false,
      upload: false,
      disk: null,
      initLoading: true,
      isInitDone: true,
    };
  },
  computed: {
    ...mapState({
      filesizes: (state) => state.fm.content.sizes,
      selectedDisk: (state) => state.fm.content.selectedDisk,
      modalName: (state) => state.fm.modal.modalName,
      sortSettings: (state) => state.fm.content.sort,
    }),
    ...mapGetters({
      disks: `fm/filemanager/diskList`,
      breadcrumb: `fm/content/breadcrumb`,
      directories: `fm/content/directories`,
      files: `fm/content/files`,
    }),
    content() {
      return this.files.length + this.directories.length;
    },
  },
  watch: {
    files() {
      if (this.files.length > 0) {
        this.store.commit("fm/content/toggleLoading", false);
      }
    },
    filesizes(v) {
      return v;
    },
    isInitDone(v) {
      if (v) {
        const route = useRoute();
        if (!route.query.disk) {
          this.store.commit("fm/content/setDisk", "tenancy", {
            root: true,
          });
        } else {
          this.store.commit("fm/content/setDisk", route.query.disk, {
            root: true,
          });
        }
        if (route.query.path) {
          this.$nextTick(() => {
            this.selectDirectory(route.query.path);
          });
        }
      }
    },
    directories() {
      if (this.directories.length > 0) {
        this.store.commit("fm/content/toggleLoading", false);
      }
    },
  },
  async beforeMount() {
    if (this.store.state.fm.content.selectedDisk === null) {
      this.isInitDone = false;
      await this.store.dispatch("fm/filemanager/initializeApp");
      this.isInitDone = true;
    }
  },
  async created() {
    this.getTags();
    await this.eventStore.on("tags-updated", () => {
      this.isInitDone = false;
      this.store.dispatch("fm/filemanager/initializeApp");
      this.isInitDone = true;
    });
    const route = useRoute();
    if (!route.query.disk) {
      this.store.commit("fm/content/setDisk", "tenancy", {
        root: true,
      });
    } else {
      this.store.commit("fm/content/setDisk", route.query.disk, {
        root: true,
      });
    }
    if (route.query.path) {
      this.$nextTick(() => {
        this.selectDirectory(route.query.path);
      });
    }
  },
  mounted() {
    this.disk = this.selectedDisk;
    this.store.commit(
      "fm/filemanager/toggleViewFolders",
      JSON.parse(window.localStorage.getItem("filemanagerViewFolders")) ?? true,
    );
    this.store.commit(
      "fm/filemanager/setView",
      window.localStorage.getItem("filemanagerView") ?? "list",
    );
    const route = useRoute();
    if (!route.query.disk) {
      this.store.commit("fm/content/setDisk", "tenancy", {
        root: true,
      });
    } else {
      this.store.commit("fm/content/setDisk", route.query.disk, {
        root: true,
      });
    }
    if (route.query.path) {
      this.$nextTick(() => {
        this.selectDirectory(route.query.path);
      });
    }
  },
  beforeUpdate() {
    // if disk changed
    if (this.disk !== this.selectedDisk) {
      this.disk = this.selectedDisk;
    }
  },
  onBeforeUnmount() {
    this.eventStore.off("tags-updated");
    this.store.commit("fm/modal/clearModal");
  },
  methods: {
    getTags() {
      this.api.get("tags").then((response) => {
        this.store.commit("tags/set_tags", response.data);
      });
    },
    saveView(e, type) {
      switch (type) {
        case "viewFolders":
          window.localStorage.setItem("filemanagerViewFolders", e);
          break;

        case "view":
          window.localStorage.setItem("filemanagerView", e);
          break;

        default:
          break;
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
    showModal(modalName) {
      // show selected modal
      this.store.commit("fm/modal/setModalState", {
        modalName,
        show: true,
      });
    },
    sortBy(field) {
      this.store.dispatch(`fm/content/sortBy`, { field, direction: null });
    },
    closeModal() {
      this.store.commit("fm/modal/clearModal");
    },
  },
};
</script>
