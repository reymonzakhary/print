<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header> {{ $t("remove") }} {{ item.name }} </template>

    <template #modal-body>
      <section class="flex max-w-lg flex-wrap">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <b>{{ item.name }}</b
          >. {{ $t("are you sure") }}
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-red-500 px-5 py-1 text-sm text-white transition-colors hover:bg-red-700"
        @click="(delete_printing_method(item.id), closeModal())"
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
  name: "PrintingMethodsRemoveItem",
  props: {
    item: Object,
  },
  emits: ["close"],
  setup() {
    const instance = getCurrentInstance();
    return { instance };
  },
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      get_printing_methods: "printing_methods/get_printing_methods",
      delete_printing_method: "printing_methods/delete_printing_method",
    }),

    closeModal() {
      this.$emit("close");
    },
  },
}; //End Export
</script>
