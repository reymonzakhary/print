<template>
  <div class="h-full">
    <AddressList
      class="h-full pb-16"
      :team="team"
      :addresses="addresses?.data"
      :is-loading="isLoading"
      @address-update="refreshAddresses"
      @address-delete="refreshAddresses"
      @address-create="refreshAddresses"
    />
  </div>
</template>

<script setup>
provide("endpoint", "teams");

const route = useRoute();
const team = computed(() => ({ id: route.params.activeTeam }));

const api = useAPI();
const isLoading = ref(true);
const { data: addresses, refresh: refreshAddresses } = await useLazyAsyncData("teams", () =>
  api.get(`teams/${team.value.id}/addresses`),
);

if (addresses) {
  isLoading.value = false;
}
</script>
