<template>
  <confirmation-modal classes="w-full sm:w-1/2 lg:w-1/4">
    <template #modal-header> {{ $t("new") }} {{ $t("address") }} </template>

    <template #modal-body>
      <NewAdressContent
        :user_id="user_id"
        :first="false"
        :extended_fields="true"
      ></NewAdressContent>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm transition-colors rounded-full text-themecontrast-400 bg-theme-400 hover:bg-theme-700"
        @click="createAddress()"
      >
        {{ $t("create new address") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapState, mapGetters, mapMutations } from "vuex";

export default {
  name: "NewAddress",
  props: {
    user_id: { type: Number },
    team: {
      type: Object,
      required: true,
    },
  },
emits: ['onClose'],
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
    ...mapMutations({
      add_address: "addresses/add_address",
    }),
    ...mapGetters({
      order_customer: "orders/order_customer",
    }),
    async createAddress() {
      // set type to string

      await this.api
        .post(`/teams/${this.team.id}/addresses`, this.inputdata)
        .then((response) => {
          this.add_address(response.data);
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
