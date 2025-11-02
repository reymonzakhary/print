<template>
  <form
    class="z-20 w-full transition-all duration-300"
    :class="{
      'absolute -top-5 h-[80vh] overflow-y-auto rounded bg-white bg-opacity-80 p-4 shadow-md backdrop-blur':
        focussed,
    }"
    style="width: calc(100% - 0.9rem)"
  >
    <span class="relative mx-auto flex items-center justify-center md:w-3/4">
      <input
        ref="mediasearch"
        v-model="search"
        type="text"
        :placeholder="$t('Search media in ') + selectedDisk"
        class="input h-auto border-2 border-theme-400 shadow-lg"
        @focus="letsFocus()"
        @keyup.esc="(setSearchResults([]), (focussed = false), $refs.mediasearch.blur())"
      />
      <span v-if="focussed" class="absolute right-2 rounded border border-b-4 p-1 text-gray-500">
        esc
      </span>
    </span>
    <button
      v-if="focussed"
      class="absolute right-4 top-4 text-lg"
      @click="((search = ''), setSearchResults([]), (focussed = false))"
    >
      <font-awesome-icon :icon="['fad', 'circle-xmark']" />
    </button>

    <section
      v-if="focussed && searchresults"
      class="flex h-[60vh] w-full flex-col justify-between text-base font-normal normal-case"
    >
      <ul class="mt-4 w-full divide-y">
        <li
          v-for="result in searchresults"
          :key="`searchresults_${result.path}`"
          class="flex w-full cursor-pointer items-center p-2 hover:font-bold"
          :class="[
            {
              'font-bold text-theme-500 dark:text-theme-400': checkSelect('files', result.path),
            },
          ]"
          @click="selectItem('files', result.path, $event)"
          @dblclick="selectAction(result.path, result.extension)"
          @contextmenu.prevent="
            (selectItem('files', result.path, $event), contextMenu(result, $event))
          "
        >
          <div class="flex w-1/3 items-center pr-4">
            <Thumbnail v-if="thisImage(result.extension)" :disk="selectedDisk" :file="result" />
            <font-awesome-icon
              v-else
              :icon="['fal', extensionToIcon(result.extension)]"
              class="mr-1 text-theme-500"
            />

            <span v-tooltip="result.basename" class="ml-2 select-none truncate">
              <span
                v-for="(part, index) in highlightName(result.basename)"
                :key="`highlight_${index}`"
                class="truncate"
                :class="{ 'font-bold text-theme-500': part.highlight }"
              >
                {{ part.text }}
              </span>
            </span>
          </div>

          <div
            class="w-1/3 cursor-pointer text-left text-sm text-theme-500 hover:underline"
            @click="(selectDirectory(result.dirname), (focussed = false))"
          >
            {{ result.dirname && result.dirname.length > 0 ? result.dirname : "/" }}
          </div>

          <div class="flex w-1/3 items-center">
            <span class="w-1/3 font-mono text-xs font-semibold tracking-wide text-gray-500">
              {{ result.extension }}
            </span>

            <span class="whitespace-no-wrap w-1/3 truncate text-xs tracking-tighter text-gray-500">
              <!-- {{ lastUpdated(result.timestamp) }} -->
              {{ timestampToDate(result.timestamp) }}
            </span>
            <span class="w-1/3 text-right font-mono text-xs font-bold text-gray-500">
              {{ formatBytes(result.size) }}
            </span>
          </div>
        </li>
      </ul>

      <Pagination
        :pagination="pagination"
        class="mt-auto"
        @pagination="
          getSearchContent({
            disk: selectedDisk,
            search: search,
            page: $event.page,
            per_page: 25,
          })
        "
      />
    </section>
  </form>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import pagination from "~/mixins/pagination";
import _ from "lodash";

export default {
  mixins: [managerhelper, helper, pagination],
  data() {
    return {
      search: "",
      focussed: false,
    };
  },
  computed: {
    ...mapState({
      selectedDisk: (state) => state.fm.content.selectedDisk,
      searchresults: (state) => state.fm.content.searchresults,
      imageExtensions: (state) => state.fm.settings.imageExtensions,
    }),
  },
  watch: {
    search: _.debounce(function (v) {
      // if (v) {
      this.getSearchContent({ disk: this.selectedDisk, search: v });
      // }
    }, 300),
  },
  methods: {
    ...mapActions({
      getSearchContent: "fm/filemanager/getSearchContent",
    }),
    ...mapMutations({
      setSearchResults: "fm/content/setSearchResults",
    }),
    thisImage(extension) {
      // extension not found
      if (!extension) return false;
      return this.imageExtensions.includes(extension.toLowerCase());
    },
    letsFocus() {
      setTimeout(() => {
        this.focussed = true;
      }, 100);
    },
    formatBytes(bytes, decimals = 2) {
      if (bytes === 0) return "0 B";

      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

      const i = Math.floor(Math.log(bytes) / Math.log(k));

      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
    },
    // highlightName(name) {
    //   if (!this.search || this.search.length === 0) {
    //     return name;
    //   }

    //   const check = new RegExp(this.search, "ig");
    //   return name.toString().replace(check, (matchedText) => {
    //     return `<strong class='highlight'>${matchedText}</strong>`;
    //   });
    // },
    highlightName(name) {
      if (!this.search || this.search.length === 0) {
        return [{ text: name, highlight: false }];
      }

      const escapedSearch = this.search.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      const check = new RegExp(`(${escapedSearch})`, "ig");
      const parts = name.split(check).map((part) => {
        if (check.test(part)) {
          return { text: part, highlight: true };
        } else {
          return { text: part, highlight: false };
        }
      });

      return parts;
    },
  },
};
</script>
