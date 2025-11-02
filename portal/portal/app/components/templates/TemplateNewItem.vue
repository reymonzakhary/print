<template>
  <ConfirmationModal classes="w-11/12 sm:w-1/2 lg:w-1/3 xl:w-1/4" @on-close="closeModal">
    <template #modal-header> {{ $t("create") }} {{ $t("new") }} {{ itemType }} </template>

    <template #modal-body>
      <!-- name -->
      <div class="my-4">
        <label for="template-name"> {{ $t("name") }}</label>

        <input
          id="template-name"
          ref="templateName"
          v-model="itemName"
          type="text"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        />
      </div>

      <!-- folder -->
      <div v-if="$store.state.templates.folders.length > 0 && itemType === 'template'" class="my-4">
        <label for="template-name">
          {{ $t("select folder") }}
          <span class="text-sm italic text-gray-500">optional</span></label
        >
        <select
          id="folder"
          v-model="folderName"
          name="folder"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        >
          <option value="null"></option>
          <option
            v-for="folder in $store.state.templates.folders"
            :key="folder.id"
            :value="folder.id"
          >
            {{ folder.name }}
          </option>
        </select>
      </div>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
        @click="addItem(itemType, itemName, folder)"
      >
        {{ $t("create new") }} {{ itemType }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapMutations } from "vuex";
import ConfirmationModal from "../global/ConfirmationModal.vue";

export default {
  components: { ConfirmationModal },
  props: {
    itemType: "",
  },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      // name for new item
      itemName: "",
      folderName: "",
    };
  },
  mounted() {
    this.focusInput();
  },
  methods: {
    ...mapMutations({
      add_template: "templates/add_template",
      add_folder: "templates/add_folder",
      add_chunk: "templates/add_chunk",
      add_snippet: "templates/add_snippet",
    }),
    /**
     * Create new item
     */
    addItem(itemType, itemName, folder) {
      switch (itemType) {
        case "template":
          this.api
            .post("modules/cms/templates", {
              name: itemName,
              folder_id: this.folderName,
            })
            .then((response) => {
              this.add_template(response.data);
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;
        case "folder":
          this.api
            .post("modules/cms/folders", {
              name: itemName,
            })
            .then((response) => {
              this.add_folder(response.data);
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;
        case "chunk":
          this.api
            .post("modules/cms/chunks", {
              name: itemName,
            })
            .then((response) => {
              this.add_chunk(response.data);
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;

        default:
          break;
      }
    },

    focusInput() {
      setTimeout(() => {
        this.$refs.templateName.focus();
      }, 100);
    },

    closeModal() {
      this.$emit("onClose");
    },
  },
};
</script>
