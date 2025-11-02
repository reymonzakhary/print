<template>
  <div class="h-full p-4 pb-0">
    <div class="flex items-stretch justify-between h-full rounded">
      <div class="w-1/6">
        <div class="flex items-center justify-between mb-2">
          <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("templates") }}
          </h2>
          <button class="text-xs capitalize rounded text-theme-500" @click="showNewItem = 'folder'">
            <font-awesome-icon :icon="['fal', 'folder-plus']" />
            {{ $t("folder") }}
          </button>
          <button
            class="text-xs capitalize rounded text-theme-500"
            @click="showNewItem = 'template'"
          >
            <font-awesome-icon :icon="['fal', 'file-plus']" />
            {{ $t("template") }}
          </button>
        </div>

        <TemplateItemList
          :folders="folders"
          :list="sortedItems(templates)"
          type="template"
          :has-folders="true"
          style="max-height: 33%"
          class="overflow-y-auto"
        ></TemplateItemList>

        <div class="flex items-center justify-between pt-4 mb-2">
          <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("chunks") }}
          </h2>
          <button class="text-xs rounded text-theme-500" @click="showNewItem = 'chunk'">
            <font-awesome-icon :icon="['fal', 'file-plus']" />
            {{ $t("chunk") }}
          </button>
        </div>

        <TemplateItemList
          :folders="folders"
          :list="sortedItems(chunks)"
          type="chunk"
          :has-folders="true"
          style="max-height: 33%"
          class="overflow-y-auto"
        ></TemplateItemList>
      </div>
      <div class="w-4/6 h-full px-4" style="max-height: calc(100% - 4rem)">
        <transition name="fade">
          <TemplateEditor v-if="Object.keys(selected_item).length > 0" class="h-full">
          </TemplateEditor>
        </transition>
      </div>

      <div class="w-1/6">
        <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
          {{ $t("general") }}
          {{ $t("tags") }}
        </h2>
        <div class="overflow-y-auto border-b dark:border-black taglist" style="max-height: 25vh">
          <ol>
            <li v-tooltip="'general indicator'" class="px-2 py-1" v-html="'[[*title]]'"></li>
            <li
              v-tooltip="'title intended for the menu'"
              class="px-2 py-1"
              v-html="'[[*menu_title]]'"
            ></li>
            <li
              v-tooltip="'SEO title for usage in the browser tab'"
              class="px-2 py-1"
              v-html="'[[*long_title]]'"
            ></li>
            <li v-tooltip="'SEO description'" class="px-2 py-1" v-html="'[[*description]]'"></li>
            <li v-tooltip="'URI of the page'" class="px-2 py-1" v-html="'[[*uri]]'"></li>
          </ol>
        </div>

        <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
          {{ $t("tags") }}
        </h2>
        <div class="overflow-y-auto border-b dark:border-black taglist" style="max-height: 25vh">
          <TemplateTagList :list="variables"></TemplateTagList>
        </div>

        <h2 class="p-2 mt-4 text-sm font-bold tracking-wide uppercase">
          {{ $t("chunks") }}
        </h2>
        <div class="overflow-y-auto border-b dark:border-black taglist" style="max-height: 25vh">
          <TemplateTagList :list="chunks"></TemplateTagList>
        </div>
      </div>
    </div>

    <transition name="fade">
      <TemplateNewItem
        v-if="showNewItem.length > 0"
        :item-type="showNewItem"
        @on-close="showNewItem = ''"
      />
    </transition>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";

export default {
  setup() {
    const api = useAPI();
    return {
      api,
    };
  },
  data() {
    return {
      showNewItem: "",
    };
  },
  head() {
    return {
      title: `${this.$t("templates")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      folders: (state) => state.templates.folders,
      templates: (state) => state.templates.templates,
      chunks: (state) => state.templates.chunks,
      snippets: (state) => state.templates.snippets,
      variables: (state) => state.templates.variables,
      selected_item: (state) => state.templates.selected_item,
    }),
  },
  created() {
    this.get_folders();
    this.get_templates();
    this.get_chunks();
    this.get_snippets();
    this.api.get("modules/cms/variables").then((response) => {
      this.set_variables(response.data);
    });
  },
  methods: {
    ...mapMutations({
      set_folders: "templates/set_folders",
      set_templates: "templates/set_templates",
      set_chunks: "templates/set_chunks",
      set_snippets: "templates/set_snippets",
      set_variables: "templates/set_variables",
    }),
    ...mapActions({
      get_folders: "templates/get_folders",
      get_templates: "templates/get_templates",
      get_chunks: "templates/get_chunks",
      get_snippets: "templates/get_snippets",
    }),
    sortedItems(items) {
      return _.sortBy(items, "sort");
    },
  },
};
</script>

<style scoped>
/* ==== Style Scrollbar ==== */
.taglist {
  scrollbar-color: rebeccapurple green;
}
.taglist::-webkit-scrollbar {
  width: 6px;
}

.taglist::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  border-radius: 10px;
  background-color: #ececec;
}

.taglist::-webkit-scrollbar-thumb {
  background-color: darkgrey;
  border-radius: 10px;
}

.taglist::-webkit-scrollbar-thumb:hover {
  background-color: #8f8f8f;
}
</style>
