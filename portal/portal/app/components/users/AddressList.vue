<template>
  <div style="max-height: calc(100vh - 6rem); height: 100%" class="overflow-y-auto">
    <AddressFormModal
      v-if="showAddressFormModal"
      :address="selectedAddress"
      :full-name-of-user="user && userFullName"
      :disabled="disabled"
      :is-creating-for-context="isContext"
      @create-address="handleAddressCreate"
      @update-address="handleAddressUpdate"
      @close-modal="showAddressFormModal = false"
    />
    <RemoveAddressModal
      v-if="showRemoveAddressModal"
      @close-modal="showRemoveAddressModal = false"
      @confirm-delete="handleAddressDelete"
    />

    <UICardHeader rounded-full>
      <template #left>
        <UICardHeaderTitle :icon="['fad', 'address-card']" :title="title ?? $t('Addresses')" />
      </template>

      <template #right>
        <UIButton
          v-if="permissions.includes('users-addresses-create')"
          :icon="['fad', 'address-card']"
          :disabled="isLoading"
          @click="openCreateAddressModal"
        >
          {{ $t("Add address") }}
        </UIButton>
      </template>
    </UICardHeader>

    <ul v-if="isLoading">
      <li v-for="i in 3" :key="`skel_${i}`"><TheUserAddressSkeleton /></li>
    </ul>
    <ul v-else>
      <li v-for="address in addresses" :key="`add_${address.id}`">
        <UserAddressCard
          :address="address"
          :disabled="disabled"
          @delete-address="openRemoveAddressModal(address)"
          @update-address="openUpdateAddressModal(address)"
        />
      </li>
    </ul>

    <TheAddressListZeroState v-if="!isLoading && addresses.length === 0" />
  </div>
</template>

<script>
export default {
  name: "AddressList",
  inject: ["endpoint"],
  props: {
    user: {
      type: Object,
      required: false,
      default: () => {},
    },
    team: {
      type: Object,
      required: false,
      default: () => {},
    },
    addresses: {
      type: Array,
      default: () => [],
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    emitOnly: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    isContext: {
      type: Boolean,
      default: false,
    },
    isShowModal: {
      type: Boolean,
      default: null,
    },
  },
  emits: ["address-create", "address-delete", "address-update", "onCreate", "onDelete", "onUpdate"],
  setup() {
    const api = useAPI();
    const authStore = useAuthStore();
    const { handleError } = useMessageHandler();
    const { addToast } = useToastStore();
    return {
      permissions: authStore.permissions,
      api,
      handleError,
      addToast,
    };
  },
  data() {
    return {
      showAddressFormModal: false,
      showRemoveAddressModal: false,
      selectedAddress: null,
    };
  },
  watch: {
    isShowModal(newVal) {
      if (newVal !== true) {
        this.showAddressFormModal = newVal;
      }
    },
  },
  computed: {
    userFullName() {
      return `${this.user.profile.first_name} ${this.user.profile.last_name}`;
    },
    apiEndpoint() {
      if (this.endpoint === "teams") {
        return `${this.endpoint}/${this.team.id}/addresses`;
      } else {
        return `${this.endpoint}/${this.user.id}/addresses`;
      }
    },
  },
  methods: {
    openCreateAddressModal() {
      this.selectedAddress = null;
      this.showAddressFormModal = true;
    },
    handleAddressCreate(address) {
      if (this.emitOnly) {
        return this.$emit("onCreate", address);
      }
      this.api
        .post(`${this.apiEndpoint}`, address)
        .then(() => {
          this.showAddressFormModal = false;
          this.addToast({
            message: this.$t("Address created successfully"),
            type: "success",
          });
          // Delay 'address-create' event to avoid DOMException error.
          this.$nextTick(() => {
            this.$emit("address-create");
          });
        })
        .catch((err) => this.handleError(err));
    },
    openRemoveAddressModal(address) {
      this.selectedAddress = address;
      this.showRemoveAddressModal = true;
    },
    handleAddressDelete() {
      if (this.emitOnly) {
        this.$emit("onDelete", this.selectedAddress);
        this.showRemoveAddressModal = false;
        return;
      }
      this.api
        .delete(`/${this.apiEndpoint}/${this.selectedAddress.id}`)
        .then(() => {
          this.showRemoveAddressModal = false;
          this.$emit("address-delete");
          this.addToast({
            message: this.$t("Address deleted successfully"),
            type: "success",
          });
        })
        .catch((err) => {
          this.handleError(err);
        })
        .finally(() => {
          this.selectedAddress = null;
        });
    },
    openUpdateAddressModal(address) {
      this.selectedAddress = this.cleanAddress(address);
      this.showAddressFormModal = true;
    },
    handleAddressUpdate(address) {
      if (this.emitOnly) {
        if (address.id === undefined) address.id = this.selectedAddress.id;
        return this.$emit("onUpdate", address);
      }
      this.api
        .put(`/${this.apiEndpoint}/${this.selectedAddress.id}`, address)
        .then(() => {
          this.showAddressFormModal = false;
          this.addToast({
            message: this.$t("Address updated successfully"),
            type: "success",
          });
          this.$nextTick(() => {
            this.$emit("address-update");
          });
          this.selectedAddress = null;
        })
        .catch((err) => {
          this.handleError(err);
        });
    },
    cleanAddress(address) {
      if (!address) return {};
      return {
        ...address,
        number: address.number ? address.number.trim() : "",
        country: address.country,
      };
    },
  },
};
</script>
