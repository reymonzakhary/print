<template>
   <div class="fm-additions-file-list">
      <!-- {{selectedItems}} -->
      <div
         class="d-flex justify-content-between"
         v-for="(item, index) in selectedItems"
         v-bind:key="index"
      >
         <div class="w-75 text-truncate">
            <span v-if="item.type === 'dir'">
               <font-awesome-icon
                  :icon="['fal', 'folder']"
                  class="mr-1 text-theme-500"
               />
               {{ item.basename }}
            </span>
            <span v-else>
               <font-awesome-icon
                  :icon="['fal', extensionToIcon(item.extension)]"
                  class="mr-1 text-theme-500"
               />
               {{ item.basename }}
            </span>
         </div>
         <div class="text-right" v-if="item.type === 'file'">
            {{ bytesToHuman(item.size) }}
         </div>
      </div>
   </div>
</template>

<script>
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
   name: "SelectedFileList",
   mixins: [helper],
   computed: {
      /**
       * Selected files and folders
       * @returns {*}
       */
      selectedItems() {
         return this.$store.getters["fm/content/selectedList"];
      },
   },
};
</script>

<style lang="scss">
.fm-additions-file-list {
   .far {
      padding-right: 0.5rem;
   }
}
</style>
