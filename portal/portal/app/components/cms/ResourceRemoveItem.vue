<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("remove") }} {{ item.title }}</span>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <b>{{ item.title }}</b
          >. {{ $t("are you sure") }}

          <div
            v-if="type === 'resource'"
            class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800"
          >
            <ul>
              <li>{{ item.title }}</li>
              <li v-if="item.long_title">{{ item.long_title }}</li>
              <li v-if="item.description">{{ item.description }}</li>
              <hr />
              <li v-if="item.resource_id">{{ item.resource_id }}</li>
              <li v-if="item.created_by">
                created by: {{ item.created_by["email"] }}
              </li>
              <li v-if="item.created_at">
                created at: {{ moment(item.updated_at) }}
              </li>
            </ul>
          </div>
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
import { mapActions, mapMutations } from "vuex";
import moment from "moment";

export default {
  name: "ResourceRemoveItem",
  props: {
    item: Object,
    type: String,
  },
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
  methods: {
    ...mapActions({
      get_tree: "resources/get_tree",
    }),
    ...mapMutations({
      set_resource: "resources/set_resource",
    }),
    deleteItem() {
      switch (this.type) {
        case "tree":
          this.api
            .delete(`modules/cms/tree/${this.item.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_tree();
              this.set_resource({});
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;

        case "resource":
          this.api
            .delete(`modules/cms/resources/${this.item.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_tree();
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
      this.$emit("on-close");
    },
  },
};
</script>
