<template>
  <div>
    <section class="mt-4 flex w-full cursor-pointer items-center justify-between py-1 pr-2">
      <div
        class="flex items-center text-right text-xs font-bold uppercase text-theme-500"
        :class="{
          'w-1/2': store.state.fm.filemanager.filesView === 'list',
          'w-full': store.state.fm.filemanager.filesView === 'grid',
        }"
      >
        <div @click="selectAll">
          <font-awesome-icon
            :icon="['fal', allSelected ? 'square-check' : 'square']"
            class="text-theme-500"
            :class="store.state.fm.filemanager.filesView === 'list' ? 'mx-5' : 'ml-5 mr-2'"
          />
          <span v-if="store.state.fm.filemanager.filesView === 'grid'">
            {{ $t("select all ") }}
          </span>
        </div>

        <div
          v-if="store.state.fm.filemanager.filesView === 'grid'"
          class="ml-auto flex h-6 items-stretch pr-2"
        >
          <select
            id="sortBy"
            name="sortBy"
            class="input rounded-none rounded-l px-2 py-0"
            @change="sortField = $event.target.value"
          >
            <option value="name">{{ $t("name") }}</option>
            <option value="type">{{ $t("type") }}</option>
            <option value="date">{{ $t("updated at") }}</option>
            <option value="size">{{ $t("filesize") }}</option>
          </select>
          <button
            class="border px-2 hover:bg-theme-100"
            @click="sortDirection === 'down' ? (sortDirection = 'up') : (sortDirection = 'down')"
          >
            <font-awesome-icon
              :icon="['fad', sortDirection === 'down' ? 'sort-alpha-down' : 'arrow-up-a-z']"
            />
          </button>

          <button
            class="rounded-r-sm bg-theme-400 px-2 py-1 text-themecontrast-400 hover:bg-theme-500"
            @click="sortBy(sortField, sortDirection)"
          >
            {{ $t("sort") }}
          </button>
        </div>

        <div v-if="store.state.fm.filemanager.filesView === 'list'" @click="sortBy('name')">
          {{ $t("name") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'name'"
            :icon="['fad', sortSettings.direction === 'down' ? 'sort-alpha-down' : 'arrow-up-a-z']"
            class="ml-1 text-black"
          />
        </div>
      </div>

      <div v-if="store.state.fm.filemanager.filesView === 'list'" class="flex w-1/2 items-center">
        <div class="w-1/3 text-xs font-bold uppercase text-theme-500" @click="sortBy('type')">
          {{ $t("type") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'type'"
            :icon="[
              'fad',
              sortSettings.direction === 'down' ? 'sort-shapes-down' : 'sort-shapes-up',
            ]"
            class="ml-1 text-black"
          />
        </div>

        <div
          v-if="store.state.fm.filemanager.filesView === 'list'"
          class="w-1/3 text-xs font-bold uppercase text-theme-500"
          @click="sortBy('date')"
        >
          {{ $t("updated") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'date'"
            :icon="[
              'fad',
              sortSettings.direction === 'down' ? 'sort-numeric-down' : 'sort-numeric-up',
            ]"
            class="ml-1 text-black"
          />
        </div>

        <div
          v-if="store.state.fm.filemanager.filesView === 'list'"
          class="mr-3 w-1/3 text-right text-xs font-bold uppercase text-theme-500"
          @click="sortBy('size')"
        >
          {{ $t("filesize") }}
          <font-awesome-icon
            v-if="sortSettings.field === 'size'"
            :icon="['fad', sortSettings.direction === 'down' ? 'sort-size-down' : 'sort-size-up']"
            class="ml-1 text-black"
          />
        </div>
      </div>
    </section>

    <div
      v-if="files.length === 0 && directories.length === 0"
      class="border-grey-200 mx-2 mt-2 rounded border px-2 py-10 italic text-gray-500 dark:border-black"
      @contextmenu.prevent="pasteMenu($event)"
    >
      {{ $t("no files") }}
    </div>

    <FilesPlaceholder v-else-if="loading" />

    <div v-else id="file-wrapper" class="w-full xl:pr-6">
      <transition-group name="slide">
        <span
          key="file-wrapper"
          class="relative ml-1 mt-2 flex h-full flex-wrap content-start justify-start overflow-y-auto rounded-t pb-16 md:pb-0"
          :class="{
            'shadow-md shadow-gray-200 dark:shadow-gray-900':
              store.state.fm.filemanager.filesView === 'list',
          }"
          :style="
            !breadcrumb || (breadcrumb.length > 1 && breadcrumb[1].path === '/')
              ? 'height: calc(100vh - 31.2rem)'
              : 'height: calc(100vh - 19rem)'
          "
        >
          <!-- Virtual scrolling for LARGE amount of files/folders -->
          <RecycleScroller
            v-if="
              store.state.fm.filemanager.filesView === 'list' &&
              store.state.fm.filemanager.viewFolders
            "
            :key="`manyfolderslist`"
            v-slot="{ item }"
            :items="directories"
            :item-size="37"
            :prerender="50"
            :page-mode="true"
            key-field="basename"
            class="h-auto w-full"
          >
            <FolderList
              :key="`folder-${item.basename}`"
              :directory="item"
              :selecting="selecting"
              :multiple="multiple"
              @on-context-menu="handleContextMenu"
            />
          </RecycleScroller>

          <RecycleScroller
            v-if="
              store.state.fm.filemanager.filesView === 'grid' &&
              store.state.fm.filemanager.viewFolders
            "
            :key="`manyfoldersgrid`"
            v-slot="{ item }"
            :items="directories"
            :item-size="200"
            :item-secondary-size="180"
            :prerender="50"
            :grid-items="gridItems"
            :emit-resize="true"
            :page-mode="true"
            list-class="flex"
            key-field="basename"
            class="h-auto w-full"
            @resize="resized"
          >
            <FolderGrid
              :key="`folder-${item.basename}`"
              :directory="item"
              :selecting="selecting"
              :multiple="multiple"
            />
          </RecycleScroller>

          <RecycleScroller
            v-if="store.state.fm.filemanager.filesView === 'list'"
            :key="`manyfileslist`"
            v-slot="{ item }"
            :items="files"
            :item-size="37"
            key-field="basename"
            class="h-auto w-full"
            :prerender="50"
          >
            <FileList
              :key="`folder-${item.basename}`"
              :file="item"
              :selecting="selecting"
              :multiple="multiple"
              @on-context-menu="handleContextMenu"
            />
          </RecycleScroller>

          <RecycleScroller
            v-if="store.state.fm.filemanager.filesView === 'grid'"
            :key="`manyfilesgrid`"
            v-slot="{ item }"
            :items="files"
            :item-size="200"
            :item-secondary-size="180"
            :prerender="50"
            :grid-items="gridItems"
            :emit-resize="true"
            key-field="basename"
            list-class="flex"
            class="overflow h-auto w-full"
          >
            <FileGrid
              :key="`folder-${item.basename}`"
              :file="item"
              :selecting="selecting"
              :multiple="multiple"
            />
          </RecycleScroller>
        </span>
      </transition-group>

      <div
        class="mx-1 flex w-full items-center justify-between rounded-b bg-gray-200 px-2 py-1 font-mono text-xs dark:bg-gray-900"
      >
        <div class="mr-4 flex shrink-0 font-bold">
          {{ selected.directories.length + selected.files.length + " " + $t("selected") }}
        </div>

        <div class="flex w-full truncate text-xs">
          <span v-for="(directory, i) in selected.directories" :key="'dir_' + i" class="group flex">
            {{ directory }}
            <span v-if="i !== selected.directories.length - 1"> - </span>
          </span>

          <span v-for="(file, i) in selected.files" :key="'file_' + i" class="group flex">
            {{ file }}
            <span v-if="i !== selected.files.length - 1"> - </span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import { mapGetters, useStore } from "vuex";

export default {
  mixins: [managerhelper],
  props: {
    selecting: Boolean,
    multiple: Boolean,
  },
  setup() {
    const store = useStore();
    return { store };
  },
  data() {
    return {
      allSelected: false,
      sortField: "name",
      sortDirection: "down",
      gridItems: 6,
    };
  },
  computed: {
    ...mapGetters({
      files: "fm/content/files",
      directories: "fm/content/directories",
      breadcrumb: `fm/content/breadcrumb`,
    }),
    loading() {
      return this.store.state.fm.content.contentLoading;
    },
    sortSettings() {
      return this.store.state.fm.content.sort;
    },

    selected() {
      return this.store.state.fm.content.selected;
    },
  },
  watch: {
    selected: {
      deep: true,
      handler() {
        this.checkAllSelected();
      },
    },
  },
  mounted() {
    if (this.files && this.directories) {
      this.checkAllSelected();
    }
  },
  methods: {
    handleContextMenu(event, item) {
      this.contextMenu(item, event);
    },
    resized() {
      this.gridItems = Math.round(document.getElementById("file-wrapper").offsetWidth / 170) - 1;
    },
    sortBy(field, direction) {
      this.store.dispatch(`fm/content/sortBy`, {
        field,
        direction: direction ?? null,
      });
    },
    selectAll() {
      this.directories.forEach((directory) => {
        this.selectItem("directories", directory.path, "add");
      });
      this.files.forEach((file) => {
        this.selectItem("files", file.path, "add");
      });
    },
    checkAllSelected() {
      this.allSelected = true;
      this.directories.forEach((dir) => {
        if (this.checkSelect("directories", dir.path) === false) {
          this.allSelected = false;
        }
      });
      this.files.forEach((file) => {
        if (this.checkSelect("files", file.path) === false) {
          this.allSelected = false;
        }
      });
    },
  },
};
</script>

<style>
input:checked + svg {
  display: block;
}
</style>
