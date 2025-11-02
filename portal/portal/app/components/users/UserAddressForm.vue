<template>
  <div>
    <!-- Address Type Selector -->
    <AddressTypeSelector
      v-if="!isCreatingForContext"
      :address-type="editMode ? addressType : false"
      class="col-span-2"
      @address-type-change="addressType = $event"
    />

    <!-- Teams Selector -->
    <div v-if="isCreatingForTeam" class="form-section">
      <label for="team">{{ $t("team") }}</label>
      <UISelector
        name="team"
        :options="reducedTeams"
        :value="teamId"
        @input="handleAddressTeamChange"
      />
      <ErrorMessage name="team" class="text-xs text-red-500" />
    </div>

    <!-- Country Selector -->
    <div class="form-section">
      <label for="country">{{ $t("country") }}</label>
      <UICountrySelector v-model="countryId" name="country" />
      <ErrorMessage name="country" class="text-xs text-red-500" />
    </div>

    <!-- Extended Fields Section (if extended_fields prop is true) -->
    <div v-if="extended_fields" class="form-section">
      <label class="label" for="full_name">{{ $t("full name") }}</label>
      <div>
        <span class="block text-xs text-gray-500">{{ $t("optional") }}</span>
        <UIInputText
          id="full_name"
          v-model="fullName"
          name="full_name"
          :placeholder="$t('full name')"
        />
        <span class="mt-1 block text-xs text-gray-500">
          {{ $t("If not filled in the customers' name will be used.") }}
        </span>
      </div>
    </div>

    <!-- Business User Section -->
    <div
      class="relative col-span-2 grid grid-cols-2 items-center rounded border border-gray-200 p-4"
    >
      <UISwitchListItem
        v-model="is_business_user"
        as="div"
        class="col-span-2 -m-4 rounded-t"
        label-class="p-4"
        name="is_business_user"
        :label="$t('business user')"
      />

      <transition name="fade">
        <div v-if="is_business_user" class="relative col-span-2 mt-3">
          <!-- Company Name Input -->
          <div class="input-section">
            <label class="label" for="company_name">{{ $t("company") }}</label>
            <UIInputText
              id="company_name"
              v-model="companyName"
              name="company_name"
              :placeholder="$t('My company')"
            />
          </div>

          <!-- VAT Number Input -->
          <div class="input-section">
            <label class="label" for="vat">{{ $t("vat") }}</label>
            <UIInputText
              id="vat"
              v-model="vatNumber"
              name="vat"
              :placeholder="$t('vat') + '09876'"
            />
          </div>

          <!-- Phone Number Input -->
          <div class="input-section">
            <label class="label" for="phone_number">{{ $t("Phone") }}</label>
            <UIInputText
              id="phone_number"
              v-model="phoneNumber"
              name="phone_number"
              placeholder="012-3456789"
            />
          </div>
        </div>
      </transition>
    </div>

    <!-- Address Section (if a country is selected) -->
    <div v-if="countryId !== '0'" class="form-section col-span-2">
      <!-- Zip Code Input -->
      <div class="input-section">
        <label class="label" for="zip_code">{{ $t("Zip Code") }}</label>
        <UIInputText
          v-model="zipCode"
          name="zip_code"
          placeholder="1234AB"
          :autocomplete="false"
          @input="addressSelected = false"
        />
        <ErrorMessage name="zip_code" class="text-xs text-red-500" />
      </div>

      <!-- Existing Addresses Section (If an address exists  in the database) -->
      <AddressSearchExistingByZipcode
        v-if="showExistingAddresses"
        :country-id="countryId"
        :zip-code="zipCode"
        class="col-span-2 my-2"
        @select-address="selectAddress"
      />

      <!-- Address Section -->
      <div v-if="showAddressFields" class="col-span-2 grid grid-cols-2">
        <!-- House Number Input (if new address) -->
        <div class="input-section">
          <label class="label" for="house_number">{{ $t("House Number") }}</label>
          <UIInputText v-model="houseNumber" name="house_number" placeholder="123" />
          <ErrorMessage name="house_number" class="text-xs text-red-500" />
        </div>

        <!-- Street Input (if new address) -->
        <div class="input-section">
          <label class="label" for="street">{{ $t("Street") }}</label>
          <UIInputText v-model="street" name="street" placeholder="Street" />
          <ErrorMessage name="street" class="text-xs text-red-500" />
        </div>

        <!-- City Input (if new address) -->
        <div class="input-section">
          <label class="label" for="city">{{ $t("City") }}</label>
          <UIInputText v-model="city" name="city" placeholder="City" />
          <ErrorMessage name="city" class="text-xs text-red-500" />
        </div>

        <!-- Region Input (if new address) -->
        <div class="input-section">
          <label class="label" for="region">{{ $t("Region") }}</label>
          <UIInputText v-model="region" name="region" placeholder="Region" />
          <ErrorMessage name="region" class="text-xs text-red-500" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "UserAddressForm",
  props: {
    // TODO: Fix so this is in line with eslint rules
    // eslint-disable-next-line vue/prop-name-casing
    extended_fields: {
      type: Boolean,
      default: true,
    },
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
  },
  emits: ["form-data-change", "onTeamChange"],
  setup() {
    const { addToast } = useToastStore();
    return { addToast };
  },
  data() {
    return {
      addressSelected: false,
      validAddressTypes: ["delivery", "invoice", ""],
      addressType: "",
      countryId: "0",
      fullName: "",
      companyName: "",
      vatNumber: "",
      phoneNumber: "",
      showExistingAddresses: false,
      showAddressFields: false,
      zipCode: "",
      houseNumber: "",
      street: "",
      city: "",
      region: "",
      is_business_user: false,
      teamId: this.$props.team,
    };
  },
  computed: {
    editMode() {
      return this.address && Object.keys(this.address).length > 0;
    },
    reducedTeams() {
      return this.teams.map((team) => {
        return {
          label: team.name,
          value: team.id,
        };
      });
    },
  },
  watch: {
    zipCode(value) {
      if ((value && value.length < 4) || this.addressSelected) return;
      this.showExistingAddresses = !this.editMode;
      this.showAddressFields = this.editMode;
    },
    $data: {
      handler: function () {
        this.$emit("form-data-change", this.retrieveFormData());
      },
      deep: true,
    },
    fullNameOfUser: {
      handler: function (newVal) {
        if (newVal && newVal === "") return;
        this.fullName = newVal;
      },
      immediate: true,
    },
  },
  created() {
    if (this.editMode) {
      this.countryId = this.address.country?.id || this.address.country || "0";
      this.fullName = this.address.full_name;
      this.is_business_user = !!this.address.company_name;
      this.companyName = this.address.company_name;
      this.vatNumber = this.address.tax_nr;
      this.phoneNumber = this.address.phone_number;
      this.zipCode = this.address.zip_code;
      this.houseNumber = this.address.number;
      this.street = this.address.address;
      this.city = this.address.city;
      this.region = this.address.region;
      this.addressType = this.address.type;
    }
  },
  methods: {
    retrieveFormData() {
      return {
        addressType: this.addressType,
        countryId: this.countryId,
        fullName: this.fullName,
        is_business_user: this.is_business_user,
        companyName: this.companyName,
        vatNumber: this.vatNumber,
        phoneNumber: this.phoneNumber,
        zipCode: this.zipCode,
        houseNumber: this.houseNumber,
        street: this.street,
        city: this.city,
        region: this.region,
      };
    },
    selectAddress(address) {
      if (address != null) {
        this.addressSelected = true;
        this.zipCode = address.zip_code;
        this.houseNumber = address.houseNumber;
        this.street = address.address;
        this.city = address.city;
        this.region = address.region;
      }

      this.showExistingAddresses = false;
      this.showAddressFields = true;
    },
    handleAddressTeamChange(teamId) {
      this.$emit("onTeamChange", teamId);
    },
  },
};
</script>

<style lang="scss" scoped>
.label {
  @apply block w-1/2 text-xs font-bold uppercase tracking-wide;
}

.form-section {
  @apply grid grid-cols-2 items-center rounded p-4;
}

.input-section {
  @apply col-span-2 grid grid-cols-2 items-center py-2;
}
</style>
