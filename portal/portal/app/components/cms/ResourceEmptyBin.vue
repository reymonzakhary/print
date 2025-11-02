<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header class="capitalize">
      {{ $t("empty bin") }}
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("This will empty the bin") }}
          {{ $t("Are you sure?") }}

          <div class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800">
            <ul>
              <li v-for="item in tree" :key="item.id">
                {{ item.title }}
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

export default {
  name: "ResourceRemoveItem",
  props: {
    tree: Array,
    type: String,
  },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    return { api };
  },
  methods: {
    ...mapActions({
      get_recycle_bin: "resources/get_recycle_bin",
    }),
    ...mapMutations({
      set_resource: "resources/set_resource",
    }),
    deleteItem() {
      this.api
        .delete(`modules/cms/tree/trash`)
        .then((response) => {
          this.handleSuccess(response);
          this.get_recycle_bin();
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },

    closeModal() {
      this.$parent.showEmptyBin = false;
      this.$emit("onClose");
    },
  },
}; //End Export
</script>
