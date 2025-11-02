<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header> {{ $t("remove") }} {{ item.name }} </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <span class="font-mono text-gray-500">#{{ item.id }}</span> 
          <b> {{ " " + item.name }}</b
          >. {{ $t("are you sure") }}
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="delete_blueprint({ id: item.id }), closeModal()"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  name: "BluePrintsRemoveItem",
  props: {
    item: Object,
    url: String,
  },
  emits: ["on-close"],
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      delete_blueprint: "blueprint/delete_blueprint",
    }),
    closeModal() {
      this.$emit("on-close");
    },
  },
}; //End Export
</script>
