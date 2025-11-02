<template>
  <div
    class="flex w-full cursor-pointer items-center justify-between bg-white px-2 py-1 hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600"
    :class="[
      {
        'font-bold text-theme-500 dark:text-theme-400': checkSelect('files', file.path),
      },
    ]"
    @click="selectItem('files', file.path, $event)"
    @dblclick="
      !selecting && permissions.includes('media-sources-update')
        ? selectAction(file.path, file.extension)
        : ''
    "
    @contextmenu.prevent="$emit('onContextMenu', $event, file)"
  >
    <section class="flex w-1/2 items-center">
      <label
        class="flex items-center text-xs font-bold uppercase tracking-wide md:mx-2"
        @click.stop="add()"
      >
        <div v-if="multiple" class="mr-2 h-3 w-3 flex-shrink-0">
          <input type="checkbox" :checked="checkSelect('files', file.path)" />
        </div>
      </label>

      <Thumbnail v-if="thisImage(file.extension)" key="file-thumbnail" :disk="disk" :file="file" />

      <font-awesome-icon
        v-if="!thisImage(file.extension)"
        key="file-icon"
        :icon="['fal', extensionToIcon(file.extension)]"
        class="mr-1 text-theme-500"
      />
      <p class="mx-4 my-1 w-full select-none truncate text-sm">
        {{ file.basename }}
      </p>
    </section>

    <section class="flex w-1/2 items-center">
      <span class="w-1/3 font-mono text-xs font-semibold tracking-wide text-gray-500">
        {{ file.extension }}
      </span>

      <span class="whitespace-no-wrap w-1/3 truncate text-xs tracking-tighter text-gray-500">
        <!-- {{ lastUpdated }} -->
        {{ timestampToDate(file.timestamp) }}
      </span>
      <span class="w-1/3 text-right font-mono text-xs font-bold text-gray-500">
        {{ formatBytes(file.size) }}
      </span>
    </section>
  </div>
  <!-- </div> -->
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
      if (bytes === 0) return "0 B";

      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

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
