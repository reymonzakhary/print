<template>
  <div v-if="subDirectories && subDirectories.length <= 500">
    <ul class="pl-3">
      <li v-for="(directory, index) in subDirectories" :key="index" class="draggable select-none">
        <!-- {{ directory }} -->
        <!-- <p
               class="max-w-full px-1 truncate transition-colors duration-75 rounded hover:bg-gray-100 dark:hover:bg-black"
               :title="directory.basename"
               :class="{ 'bg-theme-100': isDirectorySelected(directory.path) }"
            ></p> -->
        <p
          class="max-w-full truncate rounded px-1 transition-colors duration-75 hover:bg-gray-100 dark:hover:bg-black"
          :title="directory.basename"
          :class="{
            'bg-theme-100 dark:bg-theme-900': isDirectorySelected(directory.path),
          }"
        >
          <span
            class="inline-block w-3"
            :class="directory.props.hasSubdirectories ? 'cursor-pointer' : ''"
            @click.stop.prevent="
              loadingBranche !== directory.id || directory.props.subdirectoriesLoaded
                ? showSubdirectories(
                    directory.path,
                    directory.props.showSubdirectories,
                    directory.id,
                  )
                : ''
            "
          >
            <font-awesome-icon
              v-if="
                directory.props.hasSubdirectories &&
                loadingBranche === directory.id &&
                !directory.props.subdirectoriesLoaded &&
                !directory.props.showSubdirectories
              "
              :icon="['fad', 'spinner-third']"
              class="-ml-1 text-sm text-theme-500"
              spin
            />
            <font-awesome-icon
              v-if="directory.props.hasSubdirectories"
              :icon="['fas', arrowState(index) ? 'caret-down' : 'caret-right']"
            />
          </span>
          <font-awesome-icon
            :icon="[
              arrowState(index) ? 'fad' : 'fas',
              arrowState(index)
                ? 'folder-open'
                : directory.path === 'orders' || directory.path === 'Providers'
                  ? 'folder-gear'
                  : 'folder',
            ]"
            class="handle mr-1 cursor-move text-theme-500"
          />
          <a
            class="cursor-pointer hover:text-theme-500"
            :class="{
              'text-theme-500': isDirectorySelected(directory.path),
            }"
            @click="
              loadingBranche !== directory.id || directory.props.subdirectoriesLoaded
                ? selectDirectory(directory.path, directory.id)
                : ''
            "
          >
            {{ directory.basename }}
          </a>
        </p>
        <transition name="slide">
          <branch
            v-show="arrowState(index)"
            v-if="directory.props.hasSubdirectories"
            :parent-id="directory.id"
          />
        </transition>
      </li>
    </ul>
  </div>

  <div v-else class="flex items-center pl-6 italic text-gray-500">
    <font-awesome-icon :icon="['fal', 'triangle-exclamation']" />
    <font-awesome-icon :icon="['fal', 'list-tree']" class="mr-1" />
    {{ $t("too many directories to show") }}
  </div>
</template>

<script>
import { mapGetters } from "vuex";
// import Sortable from '~/plugins/directives/sortable'; // DOCS: https://github.com/SortableJS/Sortable

// const sortOptions = {
//    draggable:        '.draggable',
//    handle:           '.handle',
//    group:            'shared',
//    animation:        150,
//    fallbackOnBody:   true,
//    swapThreshold:    0.65,
//    invertSwap:       true,
//    ghostClass:       'bg-theme-300',
// }

export default {
  // sortOptions,
  // directives: { Sortable },
  name: "Branch",
  props: {
    parentId: { type: Number, required: true },
  },
  data() {
    return {
      loadingBranche: false,
    };
  },
  computed: {
    subDirectories() {
      return this.$store.getters["fm/tree/directories"].filter(
        (item) => item.parentId === this.parentId,
      );
    },
  },
  methods: {
    isDirectorySelected(path) {
      return this.$store.state.fm.content.selectedDirectory === path;
    },

    arrowState(index) {
      return this.subDirectories[index].props.showSubdirectories;
    },

    showSubdirectories(path, showState, id) {
      if (showState) {
        // hide
        this.$store.dispatch("fm/tree/hideSubdirectories", path);
      } else {
        // show
        this.loadingBranche = id;
        this.$store.dispatch("fm/tree/showSubdirectories", path);
      }
    },

    selectDirectory(path, id) {
      // only if this path not selected
      if (!this.isDirectorySelected(path)) {
        this.loadingBranche = id;
        this.$store.dispatch("fm/content/selectDirectory", {
          path,
          history: true,
        });
      }
    },
  },
};
</script>

<style></style>
