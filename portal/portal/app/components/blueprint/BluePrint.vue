<template>
  <div
    class="left-0 w-full"
    :class="expand === true ? 'fixed w-screen h-screen top-12' : 'absolute h-full w-full top-0'"
  >
    <div
      class="flex items-center justify-between w-full p-2 text-sm shadow-md shadow-gray-200 dark:shadow-gray-900 rounded-t-md"
      :class="{
        'bg-white text-theme-900': mode === 'light',
        'bg-gray-800 text-white': mode === 'dark',
      }"
    >
      <h2 class="text-xl text-center">
        {{ selected_blueprint.name }} -
        <span class="text-sm text-gray-200">
          {{ selected_blueprint.ns }}
        </span>
      </h2>

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
              mode === 'dark' ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
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
        <button
          class="px-2 py-1 text-sm transition-colors hover:bg-gray-100"
          @click="$emit('onClose')"
        >
          {{ $t("close") }}
          <font-awesome-icon :icon="['fad', 'circle-xmark']" class="ml-2" />
        </button>
      </div>
    </div>

    <div
      class="relative w-full h-full overflow-hidden bg-white shadow-md shadow-gray-200 dark:shadow-gray-900 rounded-b-md dark:bg-gray-800"
    >
      <MonacoEditor
        ref="editor"
        v-model="code"
        language="json"
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

export default {
  name: "TextEdit",
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      loading: false,
      id: "",
      monaco: {},
      editor: "",
      code: "",
      mode: this.$store.state.theme.active_theme,
      expand: false,
    };
  },
  computed: {
    ...mapState({
      blueprints: (state) => state.blueprint.blueprints,
      selected_blueprint: (state) => state.blueprint.selected_blueprint,
    }),
  },
  watch: {
    code: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    selected_blueprint: {
      deep: true,
      handler(v) {
        this.code = JSON.stringify(v.configuration, null, 4);
        this.$parent.showBlueprint = false;
        return v;
      },
    },
  },
  async mounted() {
    this.loading = true;
    this.code = JSON.stringify(this.selected_blueprint.configuration, null, 4);
  },
  methods: {
    ...mapMutations({
      select_blueprint: "blueprint/select_blueprint",
      populate_blueprints: "blueprint/populate_blueprints",
      update_blueprint_config: "blueprint/update_blueprint_config",
    }),
    handleEditorLoad({ editor }) {
      window.addEventListener("resize", () => {
        editor.layout();
      });
    },
    saveFile() {
      const id = this.selected_blueprint.id;
      const blueprintCode = JSON.parse(this.code);
      this.api
        .put(`/blueprints/${id}`, {
          name: this.selected_blueprint.name,
          configuration: blueprintCode,
          ns: this.selected_blueprint.ns,
        })
        .then((response) => {
          this.$parent.showBlueprint = false;
          this.api.get(`/blueprints`).then((response) => {
            this.populate_blueprints(response.data);
            this.select_blueprint({});
            setTimeout(() => {
              this.select_blueprint(this.blueprints.find((x) => x.id === id));
            }, 300);
          });
          this.handleSuccess(response);
        })
        .catch((error) => this.handleError(error));
    },
    resize() {
      setTimeout(() => {
        this.editor.layout();
      }, 100);
    },
    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
