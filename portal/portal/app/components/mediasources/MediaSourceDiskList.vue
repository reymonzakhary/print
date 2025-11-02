<template>
  <div class="flex gap-2">
    <button
      v-for="(disk, index) in disks"
      :key="index"
      class="flex w-1/3 flex-col items-center rounded p-2 text-sm font-bold uppercase tracking-wide text-theme-500 shadow-md"
      :class="[
        disk === selectedDisk
          ? 'bg-theme-400 text-themecontrast-400 shadow-theme-50'
          : 'bg-white shadow-gray-200',
      ]"
      @click="selectDisk(disk)"
    >
      <font-awesome-icon :icon="['fad', diskicon(disk)]" class="mx-auto mb-4 text-7xl" />

      {{ disk }}

      <transition name="slide">
        <FMSizes
          v-if="filesizes && filesizes[disk]"
          :data="filesizes[disk]"
          :mode="2"
          :bar-height="2"
          class="mt-2"
        />
      </transition>
    </button>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
export default {
  name: "DiskList",
  emits: ["diskSelected"],
  computed: {
    ...mapState({
      filesizes: (state) => state.fm.content.sizes,
      selectedDisk: (state) => state.fm.content.selectedDisk,
    }),
    ...mapGetters({
      disks: `fm/filemanager/diskList`,
    }),
  },
  async created() {
    if (this.disks.length === 0) {
      await this.$store.dispatch("fm/filemanager/initializeApp");
    }
  },

  mounted() {
    if (this.disks.length > 0) {
      // this.$parent.$parent.disk = this.disks[0];
    }
  },

  methods: {
    selectDisk(disk) {
      if (this.selectedDisk !== disk) {
        this.$store.dispatch("fm/filemanager/selectDisk", {
          disk,
          //   manager: this.manager,
        });
        this.$emit("diskSelected", disk);
      }
    },
    diskicon(disk) {
      let icon = "";
      switch (disk) {
        case "assets":
          icon = "cubes";
          break;

        case "orders":
          icon = "file-invoice-dollar";
          break;

        case "campaigns":
          icon = "circle-bolt";
          break;

        case "ftp":
          icon = "table-tree";
          break;

        case "dropbox":
          icon = "dropbox";
          break;

        case "googledrive":
          icon = "google-drive";
          break;

        case "providers":
          icon = "pen-paintbrush";
          break;

        case "tenancy":
          icon = "warehouse-full";
          break;

        default:
          icon = "hard-drive";
          break;
      }

      return icon;
    },
  },
};
</script>
