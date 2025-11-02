<!-- TODO: Refactor all instances of this component to use the updated version in future development -->
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
        {{ $t("create") }} {{ $t("new") }} {{ $t("address") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";

export default {
  name: "NewAddress",
  props: { user_id: Number },
  setup() {
	const api = useAPI();
	return { api };
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
      if (this.inputdata.region === null) {
        this.inputdata.region = "";
      }
      let user_id;
      if (this.user_id != null) {
        user_id = this.user_id;
      } else {
        user_id = this.order_customer.id;
      }
      await this.api
        .post(`/users/${user_id}/addresses`, this.inputdata)
        .then((response) => {
          this.add_address(response.data);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.$parent.closeModal();
    },
  },
};
</script>
