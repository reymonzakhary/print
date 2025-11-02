<template>
  <div
    class="flex items-center justify-between w-full px-2 py-1 bg-white cursor-pointer dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
    :class="[
      {
        'text-theme-500 font-bold dark:text-theme-400': checkSelect('directories', directory.path),
      },
    ]"
    @click="selectItem('directories', directory.path, $event)"
    @dblclick.stop="selectDirectory(directory.path)"
    @contextmenu.prevent="$emit('onContextMenu', $event, directory)"
  >
    <section class="flex items-center w-1/2">
      <label
        class="flex items-center text-xs font-bold tracking-wide uppercase md:mx-2"
        @click.stop="add(directory)"
      >
        <div v-if="multiple" class="flex-shrink-0 w-3 h-3 mr-2">
          <input type="checkbox" :checked="checkSelect('directories', directory.path)" />
        </div>
      </label>

      <font-awesome-icon
        v-if="directory.path === 'orders' || directory.path === 'Providers'"
        :icon="['fas', 'folder-gear']"
        class="mr-1 text-theme-500"
      />

      <font-awesome-icon v-else :icon="['fas', 'folder']" class="mr-1 text-theme-500" />
      <p class="w-full mx-4 my-1 text-sm truncate select-none">
        {{ directory.basename }}
      </p>
    </section>

    <section class="flex items-center w-1/2">
      <span class="w-1/3 font-mono text-xs font-bold tracking-wide text-gray-500 truncate">
        {{ $t("directory") }}
      </span>
      <span class="w-1/3 text-xs tracking-tighter text-gray-500 truncate whitespace-no-wrap">
        <!-- {{ lastUpdated }} -->
        -
      </span>
      <span class="w-1/3 text-sm font-bold text-right">
        <!-- <p>{{ formatBytes(directory.size) }}</p> -->
      </span>
    </section>
  </div>
  <!-- </div> -->
</template>

<script>
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

import { mapState } from "vuex";

export default {
  mixins: [managerhelper, helper],
  props: {
    directory: Object,
    selecting: Boolean,
    multiple: Boolean,
  },
  emits: ["onContextMenu"],
  data() {
    return {
      disk: "",
    };
  },
  computed: {
    ...mapState({
      selected: (state) => state.fm.content.selected,
      selectedDisk: (state) => state.fm.content.selectedDisk,
    }),
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
    add(directory) {
      const alreadySelected = this.selected["directories"].includes(directory.path);
      if (!alreadySelected) {
        // add new selected item
        this.$store.commit(`fm/content/setSelected`, {
          type: "directories",
          path: directory.path,
        });
      } else {
        // remove selected item
        this.$store.commit(`fm/content/removeSelected`, {
          type: "directories",
          path: directory.path,
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
