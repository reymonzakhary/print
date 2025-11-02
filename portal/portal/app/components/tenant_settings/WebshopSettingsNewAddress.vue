<template>
  <confirmation-modal classes="w-full sm:w-1/2 lg:w-1/4">
    <template #modal-header> {{ $t("new") }} {{ $t("address") }} </template>

    <template #modal-body>
      <NewAdressContent :context_id="contextId" :first="false" :extended_fields="true" />
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-theme-400 px-4 py-1 text-sm text-themecontrast-400 transition-colors hover:bg-theme-700"
        @click="createAddress()"
      >
        {{ $t("create") }} {{ $t("new") }} {{ $t("address") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: "NewAddress",
  props: { contextId: { type: Number, default: null } },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  computed: {
    ...mapState({
      inputdata: (state) => state.addresses.inputdata,
    }),
  },
  methods: {
    async createAddress() {
      // set type to string
      if (this.inputdata.region === null) {
        this.inputdata.region = "";
      }
      let context_id;
      if (this.context_id != null) {
        context_id = this.context_id;
      } else {
        context_id = this.order_customer.id;
      }

      await this.api
        .post(`/contexts/${context_id}/addresses`, this.inputdata)
        .then((response) => {
          const context = this.$parent.contexts.find((ctx) => ctx.id === context_id).addresses;
          context.push(response.data);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.$emit("onClose");
    },
  },
};
</script>
