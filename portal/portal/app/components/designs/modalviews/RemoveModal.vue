<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header>
      <p class="capitalize">{{ $t("remove") }} {{ selected_template.name }}</p>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <b>{{ selected_template.name }} </b>
          {{ $t("from") }}<b>{{ selected_template.design_provider.name }}</b
          >. <br /><br />
          {{ $t("are you sure") }}
          <div class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800">
            <p class="text-lg font-bold">
              <span class="text-base text-gray-500"
                >#{{ selected_template.id }} </span
              >{{ selected_template.name }}
            </p>
            <p class="italic">{{ selected_template.description }}</p>

            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("created at") }}
            </p>
            <p>
              {{
                moment(selected_template.created_at).format("DD-MM-YYYY HH:MM")
              }}
            </p>
          </div>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="deleteDesignTemplate()"
      >
        {{ $t("yes") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import moment from "moment";

export default {
  name: "RemoveDesignTemplateModal",
  emits: ["on-close"],
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
  computed: {
    ...mapState({
      selected_template: (state) => state.design.selected_template,
    }),
  },
  watch: {
    selected_template(newVal) {
      return newVal;
    },
  },
  methods: {
    ...mapMutations({
      remove_selected_template: "design/remove_selected_template",
    }),
    deleteDesignTemplate() {
      this.api
        .delete(`design/provider/templates/${this.selected_template.id}`)
        .then((response) => {
          this.handleSuccess(response);
          this.remove_selected_template(this.selected_template);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.$emit("on-close");
    },
  },
}; //End Export
</script>
