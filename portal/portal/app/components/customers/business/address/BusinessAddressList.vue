<template>
  <div>
    <AddressFormModal
      v-if="showAddressFormModal || showUpdateAddressFormModal"
      :address="selectedAddress"
      :full-name-of-user="selectedMember && selectedMember.full_name"
      @create-address="handleAddressCreate"
      @update-address="handleAddressUpdate"
      @close-modal="closeAddressModal"
    />
    <RemoveAddressModal
      v-if="showRemoveAddressModal"
      @close-modal="closeAddressModal"
      @confirm-delete="handleAddressDelete"
    />
    <ul v-if="addresses.length > 0">
      <li v-for="address in addresses" :key="`add_${address.id}`">
        <UserAddressCard
          :address="address"
          :may-update="hasPermission('members-addresses-update')"
          :may-delete="hasPermission('members-addresses-delete')"
          @delete-address="openRemoveAddressModal(address)"
          @update-address="openUpdateAddressModal(address)"
        />
      </li>
    </ul>
    <ZeroState v-else :message="$t('Create an address to get started')" />
  </div>
</template>

<script setup>
const props = defineProps({
  addresses: {
    type: Array,
    required: true,
  },
  showAddressFormModal: {
    type: Boolean,
    required: true,
  },
  selectedMember: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits([
  "create-address",
  "update-address",
  "delete-address",
  "close-address-form-modal",
]);

const { t: $t } = useI18n();
const privates = usePrivateRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { permissions: newPermissions, hasPermission } = usePermissions();

const privateId = computed(() => props.selectedMember.id);
const showUpdateAddressFormModal = ref(false);
const showRemoveAddressModal = ref(false);
const selectedAddress = ref(null);

function openRemoveAddressModal(address) {
  selectedAddress.value = address;
  showRemoveAddressModal.value = true;
}

function openUpdateAddressModal(address) {
  selectedAddress.value = address;
  showUpdateAddressFormModal.value = true;
}

async function handleAddressCreate(address) {
  const newAddress = { ...address };
  if (address.full_name?.length === 0) {
    newAddress.full_name = props.selectedMember.name;
  }
  try {
    const newerAddress = await privates.createAddress(privateId.value, newAddress);
    emit("create-address", newerAddress);
    closeAddressModal();
    addToast({
      type: "success",
      message: $t("Address created successfully"),
    });
  } catch (error) {
    handleError(error);
  }
}

async function handleAddressUpdate(updatedAddress) {
  const address = {
    ...updatedAddress,
    id: selectedAddress.value.id,
  };

  try {
    const newAddress = await privates.updateAddress(privateId.value, address);
    emit("update-address", { address: address, newAddress });
    closeAddressModal();
    addToast({
      type: "success",
      message: $t("Address updated successfully"),
    });
  } catch (error) {
    handleError(error);
  }
}

async function handleAddressDelete() {
  try {
    await privates.deleteAddress(privateId.value, selectedAddress.value.id);
    emit("delete-address", selectedAddress.value);
    closeAddressModal();
    addToast({
      type: "success",
      message: $t("Address deleted successfully"),
    });
  } catch (error) {
    handleError(error);
  }
}

function closeAddressModal() {
  emit("close-address-form-modal");
  showUpdateAddressFormModal.value = false;
  showRemoveAddressModal.value = false;
  selectedAddress.value = null;
}
</script>
