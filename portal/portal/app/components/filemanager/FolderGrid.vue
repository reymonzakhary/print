<template>
  <div
    class="px-2 py-1 mx-1 bg-white rounded shadow-md cursor-pointer shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 hover:bg-gray-50"
    :class="[
      {
        'bg-theme-100 text-theme-600 dark:text-theme-400 hover:bg-theme-100': checkSelect(
          'directories',
          directory.path,
        ),
      },
    ]"
    @click="selectItem('directories', directory.path, $event)"
    @dblclick.stop="selectDirectory(directory.path)"
    @contextmenu.prevent="contextMenu(directory, $event)"
  >
    <section class="flex flex-col justify-around h-full">
      <!-- select and extention -->
      <div class="flex justify-between mb-2">
        <label
          class="flex items-center text-xs font-bold tracking-wide uppercase"
          @click.stop="add()"
        >
          <div class="flex-shrink-0 w-3 h-3 mr-2">
            <input type="checkbox" :checked="checkSelect('directories', directory.path)" />
          </div>
        </label>

        <span class="text-xs font-bold tracking-wide uppercase truncate">
          {{ $t("directory") }}
        </span>
      </div>

      <font-awesome-icon
        v-if="directory.path === 'orders' || directory.path === 'Providers'"
        :icon="['fad', 'folder-gear']"
        class="mx-auto my-2 text-theme-500"
        style="font-size: 90px"
      />

      <font-awesome-icon
        v-else
        :icon="['fad', 'folder-closed']"
        class="mx-auto my-2 text-theme-500"
        style="font-size: 90px"
      />

      <p class="w-full mt-2 font-bold truncate">{{ directory.basename }}</p>

      <!-- date and size  -->
      <!-- <div class="flex items-center justify-between">
				<p
					class="text-xs tracking-tighter text-gray-500 truncate whitespace-no-wrap"
				>
					{{ lastUpdated }}
				</p>
			</div> -->
    </section>
  </div>
</template>

<script>
import moment from "moment";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  props: {
    directory: Object,
    selecting: Boolean,
    multiple: Boolean,
  },
  mixins: [managerhelper, helper],
  data() {
    return {
      disk: "",
    };
  },
  computed: {
    // lastUpdated() {
    // 	const miliseconds = this.directory.timestamp * 1000;
    // 	const date = new Date(miliseconds);
    // 	return moment(date).format("DD-MM-YYYY HH:MM");
    // 	// return `${date.getMonth()} ${date.getDay()} ${date.getFullYear()} `
    // },
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
      const alreadySelected = this.selected["directories"].includes(this.directory.path);
      if (!alreadySelected) {
        // add new selected item
        this.$store.commit(`fm/content/setSelected`, {
          type: "directories",
          path: this.directory.path,
        });
      } else {
        // remove selected item
        this.$store.commit(`fm/content/removeSelected`, {
          type: "directories",
          path: this.directory.path,
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
