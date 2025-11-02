<template>
  <div
    :class="
      expand === true ? 'fixed w-screen h-screen top-12 left-0' : 'h-full'
    "
  >
    <div
      class="flex items-center justify-between w-full p-2 text-sm shadow-md shadow-gray-200 dark:shadow-gray-900 rounded-t-md"
      :class="{
        'bg-white text-theme-900': mode === 'light',
        'bg-gray-800 text-white': mode === 'dark',
      }"
    >
      <TemplateEditorInfo></TemplateEditorInfo>

      <div class="flex items-center">
        <font-awesome-icon
          :icon="['fal', 'sun']"
          class="ml-4"
          :class="{ 'text-yellow-500': mode === 'light' }"
        />
        <div
          class="relative w-8 h-3 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
          :class="[mode === 'dark' ? 'bg-theme-400' : 'bg-gray-300']"
        >
          <label
            for="mode"
            class="absolute left-0 w-3 h-3 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
            :class="[
              mode === 'dark'
                ? 'translate-x-6 border-theme-500'
                : 'translate-x-0 border-gray-300',
            ]"
          ></label>
          <input
            id="mode"
            type="checkbox"
            name="mode"
            class="w-full h-full appearance-none active:outline-none focus:outline-none"
            @change="mode === 'light' ? (mode = 'dark') : (mode = 'light')"
          />
        </div>
        <font-awesome-icon
          :icon="['fal', 'moon-stars']"
          :class="{ 'text-blue-500': mode === 'dark' }"
        />
      </div>

      <div>
        <button
          class="px-2 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
          @click="saveFile()"
        >
          {{ $t("save") }}
        </button>
        <button
          class="px-2 py-1 text-sm transition-colors hover:bg-gray-100"
          @click="(expand = !expand), resize()"
        >
          <font-awesome-icon :icon="['fal', 'arrows-maximize']" />
        </button>
      </div>
    </div>

    <div
      class="relative w-full h-full overflow-hidden bg-white shadow-md shadow-gray-200 dark:shadow-gray-900 rounded-b-md dark:bg-gray-800"
    >
      <MonacoEditor
        ref="editor"
        v-model="content"
        language="html"
        class="w-full h-full"
        :options="{
          theme: mode === 'dark' ? 'vs-dark' : 'vs',
          minimap: { enabled: false },
        }"
        @keydown.ctrl.s.prevent.stop="saveFile"
        @on-load="handleEditorLoad"
      />
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import * as monaco from "monaco-editor";

export default {
  setup() {
    const api = useAPI();
    const { addToast } = useToastStore();
    return { api, addToast };
  },
  data() {
    return {
      loading: false,
      content: "",
      id: "",
      theme: "vs",
      code: "",
      mode: this.$store.state.theme.active_theme,
      expand: false,
    };
  },
  computed: {
    ...mapState({
      selected_item: (state) => state.templates.selected_item,
      selected_item_type: (state) => state.templates.selected_item_type,
      variables: (state) => state.templates.variables,
      chunks: (state) => state.templates.chunks,
    }),
  },
  watch: {
    selected_item: {
      handler: function (val) {
        this.content = this.htmlDecode(val.content);
      },
      deep: true,
      immediate: true,
    },
  },
  unmounted() {
    window.removeEventListener("resize", () => {
      editor.layout();
    });
  },
  methods: {
    ...mapMutations({
      replace_selected_item_content: "templates/replace_selected_item_content",
    }),
    handleEditorLoad({ editor }) {
      window.addEventListener("resize", () => {
        editor.layout();
      });

      const customChunks = [...this.chunks].map((chunk) => {
        return {
          label: chunk.name,
          kind: monaco.languages.CompletionItemKind.Keyword,
          insertText: chunk.short_code,
          detail: "Chunk",
          range: null,
        };
      });

      monaco.languages.registerCompletionItemProvider("html", {
        provideCompletionItems: function (model, position) {
          const word = model.getWordUntilPosition(position);
          const range = {
            startLineNumber: position.lineNumber,
            endLineNumber: position.lineNumber,
            startColumn: word.startColumn,
            endColumn: word.endColumn,
          };

          // Set the range property for each completion item
          customChunks.forEach(function (tag) {
            tag.range = range;
          });
          return { suggestions: customChunks };
        },
      });
    },
    saveFile() {
      const value = this.content;
      const data = new FormData();
      const blob = new Blob([value], { type: "text/html" });
      const file = new File([blob], `${this.selected_item.name}.html`, {
        type: "text/html",
        lastModified: new Date().getTime(),
      });
      // add file
      data.append("content", file);
      data.append("_method", "put");

      this.api
        .post(
          `modules/cms/${this.selected_item_type}/${this.selected_item.id}`,
          data,
          {
            isFormData: true,
          },
        )
        .then((response) => {
          this.replace_selected_item_content(response.data.content);
          this.addToast({
            type: "success",
            message: this.$t("File Saved"),
          });
        });
    },
    htmlDecode(input) {
      const e = document.createElement("div");
      e.innerHTML = input;
      // return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
      return e.innerText;
    },
  },
};
</script>

<style lang="scss">
.cldr {
  // general styling
  width: 5px !important;
  height: 5px !important;
  cursor: pointer;

  // general arrow styling
  border: solid rgb(141, 141, 141);
  border-width: 0 1px 1px 0;
  display: inline-block;
  padding: 3px;
  top: 6px;
  left: 50px !important;

  &:hover {
    border-color: black;
  }

  &.folding {
    //  down arrow
    transform: rotate(45deg);
    -webkit-transform: rotate(45deg);
  }

  &.folding.collapsed {
    border-color: black;

    // right arrow
    transform: rotate(-45deg);
    -webkit-transform: rotate(-45deg);
  }
}
</style>
