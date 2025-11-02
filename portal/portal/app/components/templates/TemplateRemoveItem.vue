<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("remove") }} {{ itemType }} {{ item.name }}</span>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("this will remove") }}
          <b>{{ itemType }} {{ item.name }}</b
          >. {{ $t("are you sure") }}
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="deleteItem()"
      >
        {{ $t("yes") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  name: "TemplateRemoveItem",
  props: {
    item: Object,
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
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      get_templates: "templates/get_templates",
      get_folders: "templates/get_folders",
      get_chunks: "templates/get_chunks",
    }),
    deleteItem() {
      switch (this.itemType) {
        case "template":
          this.api
            .delete(`modules/cms/templates/${this.item.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_templates();
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;

        case "folder":
          this.api
            .delete(`modules/cms/folders/${this.item.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_folders();
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;

        case "chunk":
          this.api
            .delete(`modules/cms/chunks/${this.item.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_chunks();
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
    closeModal() {
      this.$emit("onClose");
    },
  },
}; //End Export
</script>
