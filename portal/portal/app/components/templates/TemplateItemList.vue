<template>
  <div>
    <template v-for="folder in folders" :key="folder.id">
      <div
        class="items-center justify-between px-1 capitalize transition-colors duration-100 rounded cursor-pointer focus:outline-none hover:text-theme-500 group"
        :class="{
          'text-theme-500 bg-gray-200': showItems === folder.id,
        }"
        @click="showItems !== folder.id ? (showItems = folder.id) : (showItems = false)"
      >
        <div class="w-full">
          <p class="flex items-center justify-between">
            <span>
              <font-awesome-icon
                :icon="['fas', showItems === folder.id ? 'caret-down' : 'caret-right']"
                class="mr-2"
              />
              <font-awesome-icon
                :key="'folder_' + folder.id"
                :icon="['fas', 'folder']"
                class="mr-2 text-theme-500"
              />
              {{ folder.name }}
            </span>

            <span class="flex">
              <button
                class="flex invisible px-2 py-1 mr-1 text-red-500 rounded-full group-hover:visible hover:bg-red-100"
                @click="showRemoveItem = folder.id"
              >
                <font-awesome-icon :icon="['fal', 'trash-can']" />
              </button>
            </span>
          </p>
        </div>

        <transition name="fade">
          <TemplateRemoveItem
            v-if="showRemoveItem === folder.id"
            :item="folder"
            :item-type="'folder'"
            @on-close="showRemoveItem = false"
          ></TemplateRemoveItem>
        </transition>
      </div>

      <transition name="slide">
        <div>
          <template v-for="item in list" :key="'item_' + item.id">
            <div v-if="showItems === folder.id" class="w-full pl-4">
              <TemplateItem
                v-if="item.folder && item.folder.id === folder.id"
                :item="item"
                :type="type"
                :folders="folders"
              ></TemplateItem>
            </div>
          </template>
        </div>
      </transition>
    </template>

    <div class="">
      <template v-for="item in list" :key="'item_' + item.id">
        <TemplateItem
          v-if="hasFolders ? item.folder === null : item"
          :item="item"
          :type="type"
          :folders="folders"
        ></TemplateItem>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    type: String,
    folders: Array,
    list: Array,
    hasFolders: Boolean,
  },
  data() {
    return {
      showItems: false,
      showRemoveItem: false,
      folderList: [],
    };
  },
  mounted() {
    setTimeout(() => {
      this.list.forEach((item) => {
        if (item.folder.length > 0) {
          this.folderList.push(item.folder.name);
        }
      });
    }, 200);
  },
};
</script>
