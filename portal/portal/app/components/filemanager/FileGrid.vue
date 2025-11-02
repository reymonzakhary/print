<template>
  <div
    class="mx-1 cursor-pointer rounded border shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
    :class="[
      {
        'border-theme-400 bg-theme-100 text-theme-600 hover:bg-theme-100 dark:text-theme-400':
          checkSelect('files', file.path),
        'border-transparent bg-white hover:bg-gray-50': !checkSelect('files', file.path),
      },
    ]"
    @click="selectItem('files', file.path, $event)"
    @dblclick="
      !selecting && permissions.includes('media-sources-update')
        ? selectAction(file.path, file.extension)
        : ''
    "
    @contextmenu.prevent="contextMenu(file, $event)"
  >
    <section class="relative flex h-full flex-col justify-around">
      <!-- select and extention -->
      <div class="absolute top-0 flex w-full justify-between px-2 pt-1">
        <label
          class="flex items-center text-xs font-bold uppercase tracking-wide"
          @click.stop="add()"
        >
          <div class="mr-2 h-3 w-3 flex-shrink-0">
            <input type="checkbox" :checked="checkSelect('files', file.path)" />
          </div>
        </label>
      </div>

      <GridThumbnail
        v-if="thisImage(file.extension)"
        key="file-thumbnail"
        :disk="disk"
        :file="file"
      />

      <font-awesome-icon
        v-if="!thisImage(file.extension)"
        key="file-icon"
        :icon="['fal', extensionToIcon(file.extension)]"
        class="mx-auto my-4 text-theme-500"
        style="font-size: 64px"
      />

      <div class="px-2 py-1">
        <p
          v-tooltip="file.basename.length > 16 ? file.basename : ''"
          class="mt-2 w-full truncate text-sm font-bold"
        >
          {{ file.basename }}
        </p>
        <span
          class="truncate rounded bg-theme-400 px-1 text-xs font-medium uppercase tracking-wide text-white"
        >
          {{ file.extension }}
        </span>
        <!-- date and size  -->
        <div
          class="whitespace-no-wrap mt-2 flex items-center justify-between truncate text-xs tracking-tighter text-gray-500"
        >
          <p class="">
            <!-- {{ lastUpdated }} -->
            {{ timestampToDate(file.timestamp) }}
          </p>
          <p class="text-right font-mono text-xs font-bold">
            {{ formatBytes(file.size) }}
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  mixins: [managerhelper, helper],
  props: {
    file: {
      type: Object,
      required: true,
    },
    selecting: Boolean,
    multiple: Boolean,
  },
  emits: ["onContextMenu"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    return { permissions };
  },
  data() {
    return {
      disk: "",
    };
  },
  computed: {
    imageExtensions() {
      return this.$store.state.fm.settings.imageExtensions;
    },
  },
  mounted() {
    this.disk = this.selectedDisk;
  },
  beforeUpdate() {
    // if disk changed
    if (this.disk !== this.selectedDisk) {
      this.disk = this.selectedDisk;
    }
  },
  methods: {
    add() {
      // search in selected array
      const alreadySelected = this.selected["files"].includes(this.file.path);
      if (!alreadySelected) {
        // add new selected item
        this.$store.commit(`fm/content/setSelected`, {
          type: "files",
          path: this.file.path,
        });
      } else {
        // remove selected item
        this.$store.commit(`fm/content/removeSelected`, {
          type: "files",
          path: this.file.path,
        });
      }
    },
    formatBytes(bytes, decimals = 2) {
      if (bytes === 0) return "0 Bytes";

      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

      const i = Math.floor(Math.log(bytes) / Math.log(k));

      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
    },
    thisImage(extension) {
      // extension not found
      if (!extension) return false;
      return this.imageExtensions.includes(extension.toLowerCase());
    },
  },
};
</script>

<style></style>
