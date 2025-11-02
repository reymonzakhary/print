<template>
  <Teleport to="body">
    <ConfirmationModal @on-close="$emit('close-modal')">
      <template #modal-header>
        {{ editMode ? $t("Edit Address") : $t("Create Address") }}
      </template>

      <template #modal-body>
        <Form ref="addressForm" @submit="createAddress">
          <UserAddressForm
            :address="address"
            :full-name-of-user="fullNameOfUser"
            :is-creating-for-team="isCreatingForTeam"
            :is-creating-for-context="isCreatingForContext"
            :teams="teams"
            :team="team"
            @form-data-change="updateAddressData"
            @on-team-change="handleTeamChange"
          />
        </Form>
      </template>

      <template #confirm-button>
        <ModalButton
          variant="success"
          :disabled="isCreatingAddress || disabled"
          @click="submitAddressForm"
        >
          {{ editMode ? $t("Update Address") : $t("Create Address") }}
        </ModalButton>
      </template>
    </ConfirmationModal>
  </Teleport>
</template>

<script>
export default {
  name: "AddressFormModal",
  props: {
    address: {
      type: Object,
      default: () => {},
    },
    fullNameOfUser: {
      type: String,
      default: "",
    },
    isCreatingForTeam: {
      type: Boolean,
      default: false,
    },
    isCreatingForContext: {
      type: Boolean,
      default: false,
    },
    teams: {
      type: Array,
      default: () => [],
    },
    team: {
      type: [Number, String, null],
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["close-modal", "update-address", "create-address", "on-team-change"],
  data() {
    return {
      isCreatingAddress: false,
      theAddress: {
        street: "",
        houseNumber: "",
        zipCode: "",
        city: "",
        region: "",
        countryId: null,
        fullName: this.fullNameOfUser,
        addressType: "",
        companyName: "",
        vatNumber: "",
        phoneNumber: "",
        is_business_user: false,
      },
    };
  },
  computed: {
    editMode() {
      return this.address && Object.keys(this.address)?.length > 0;
    },
  },
  watch: {
    address: {
      handler: function () {
        if (this.address) {
          this.theAddress = {
            street: this.address.address, // address
            city: this.address.city, // city
            companyName: this.address.company_name, // company_name
            countryId: this.address.country_id, // country_id
            fullName: this.address.full_name, // full_name
            houseNumber: this.address.number, // number
            is_business_user: this.address.is_business_user, // is_business_user
            zipCode: this.address.zip_code, // zip_code
            phoneNumber: this.address.phone_number, // phone_number
            region: this.address.region, // region
            vatNumber: this.address.tax_nr, // tax_nr
            addressType: this.address.type, // type
          };
        }
      },
      deep: true,
      immediate: true,
    },
  },
  methods: {
    handleTeamChange(teamId) {
      this.$emit("on-team-change", teamId);
    },
    updateAddressData(addressData) {
      this.theAddress = addressData;
    },
    constructAddress() {
      // TODO: Ask back-end to make business user optionable like it is in frontend
      return {
        type: this.theAddress.addressType,
        address: this.theAddress.street,
        number: this.theAddress.houseNumber,
        city: this.theAddress.city,
        region: this.theAddress.region,
        zip_code: this.theAddress.zipCode,
        country_id: this.theAddress.countryId,
        type: this.theAddress.addressType,
        full_name: this.theAddress.fullName,
        company_name: this.theAddress.companyName,
        phone_number: this.theAddress.phoneNumber,
        tax_nr: this.theAddress.vatNumber,
        is_business_user: this.theAddress.is_business_user,
      };
    },
    createAddress() {
      this.isCreatingAddress = true;
      const addressData = this.constructAddress();
      const eventType = this.editMode ? "update-address" : "create-address";
      this.$emit(eventType, addressData);
      this.isCreatingAddress = false;
    },
    submitAddressForm() {
      this.$refs.addressForm.$el.requestSubmit();
    },
    closeModal() {
      this.$emit("close-modal");
    },
  },
};
</script>
