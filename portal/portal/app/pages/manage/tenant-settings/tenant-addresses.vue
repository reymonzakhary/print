<template>
  <div class="h-full p-4">
    <section class="mx-auto grid grid-cols-2 gap-4 md:w-2/3">
      <div v-for="context in contexts" :key="context.id" class="">
        <AddressList
          :addresses="addressesMap[context.id]"
          :is-loading="isDoingStuff"
          :title="context.name"
          :is-show-modal="isShowModal"
          :disabled="isDoingStuff"
          :emit-only="true"
          is-context
          @on-create="handleCreateAddress(context.id, $event)"
          @on-delete="handleDeleteAddress(context.id, $event)"
          @on-update="handleUpdateAddress(context.id, $event)"
        />
      </div>
    </section>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();
const api = useAPI();
const contextAddressRepository = useGenericAddressRepository("contexts");
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const { theUser } = storeToRefs(useAuthStore());
const isShowModal = ref(true);

provide("endpoint", "contexts");

const contexts = ref([]);
const addressesMap = ref({});
const isDoingStuff = ref(false);

const response = await api.get("/contexts");
contexts.value = response.data;

contexts.value.forEach(async (context) => {
  addressesMap.value[context.id] = await contextAddressRepository.getAddresses(context.id);
});

async function handleCreateAddress(contextId, address) {
  if (isDoingStuff.value) return;
  try {
    isDoingStuff.value = true;

    const theAddress = { ...address };
    if (theAddress.full_name?.length === 0) {
      theAddress.full_name =
        theUser.value.profile.first_name + " " + theUser.value.profile.last_name;
    }

    const newAddress = await contextAddressRepository.createAddress(contextId, theAddress);
    addressesMap.value[contextId].push(newAddress);
    addToast({
      type: "success",
      message: $t("The address has been created successfully."),
    });
    isShowModal.value = false;
    setTimeout(() => {
      isShowModal.value = true;
    }, 100);
  } catch (error) {
    handleError(error);
  } finally {
    isDoingStuff.value = false;
  }
}

async function handleDeleteAddress(contextId, address) {
  if (isDoingStuff.value) return;
  try {
    isDoingStuff.value = true;
    await contextAddressRepository.deleteAddress(contextId, address.id);
    addressesMap.value[contextId] = addressesMap.value[contextId].filter(
      (a) => a.id !== address.id,
    );
    addToast({
      type: "success",
      message: $t("The address has been deleted successfully."),
    });
  } catch (error) {
    handleError(error);
  } finally {
    isDoingStuff.value = false;
  }
}

async function handleUpdateAddress(contextId, address) {
  if (isDoingStuff.value) return;
  try {
    isDoingStuff.value = true;
    const updatedAddress = await contextAddressRepository.updateAddress(contextId, address);
    const index = addressesMap.value[contextId].findIndex((a) => a.id === address.id);
    addressesMap.value[contextId][index] = updatedAddress;
    addToast({
      type: "success",
      message: $t("The address has been updated successfully."),
    });
    isShowModal.value = false;
    setTimeout(() => {
      isShowModal.value = true;
    }, 100);
  } catch (error) {
    handleError(error);
  } finally {
    isDoingStuff.value = false;
  }
}
</script>

<style scoped>
/* Your styles here */
</style>
