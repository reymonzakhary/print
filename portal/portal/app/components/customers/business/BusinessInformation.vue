<template>
  <UICardHeader>
    <template #left>
      <UICardHeaderTitle
        :icon="['fal', 'building']"
        :title="$t('Company Information')"
      />
    </template>
    <template v-if="showEdit" #right>
      <UIButton
        :icon="['fal', 'pencil']"
        :disabled="isLoading"
        @click="handleEditBusiness"
      />
    </template>
  </UICardHeader>
  <UICard>
    <div class="grid grid-cols-[100px_1fr] items-center gap-4 p-2 py-4">
      <img :src="company.logo" :alt="`${$t('Logo of')} ${company.name}`" />
      <div>
        <h1 class="text-xl font-bold">{{ company.name }}</h1>
        <Nuxt-Link
          :to="company.website"
          target="_blank"
          class="hover:underline"
        >
          <h2 class="italic text-gray-700">
            {{ company.website }}
          </h2>
        </Nuxt-Link>
      </div>
    </div>
    <hr class="mx-6 my-2">
    <div class="p-2 flex flex-col gap-4 overflow-hidden">
      <div class="list-item">
        <span class="capitalize">{{ $t("country") }}</span>
        <span v-tooltip.bottom="company.country" class="font-semibold">{{
          company.country
        }}</span>
      </div>
      <div class="list-item">
        <span class="capitalize">{{ $t("city") }}</span>
        <span v-tooltip.bottom="company.city" class="font-semibold">{{
          company.city
        }}</span>
      </div>
      <div class="list-item">
        <span class="capitalize">{{ $t("Address") }}</span>
        <span v-tooltip.bottom="company.address" class="font-semibold">{{
          company.address
        }}</span>
      </div>
      <div class="list-item">
        <span class="capitalize">{{ $t("KVK") }}</span>
        <span v-tooltip.bottom="company.kvk" class="font-semibold">{{
          company.kvk
        }}</span>
      </div>
      <div class="list-item">
        <span class="capitalize">{{ $t("BTW") }}</span>
        <span v-tooltip.bottom="company.btw" class="font-semibold">{{
          company.btw
        }}</span>
      </div>
    </div>
  </UICard>
</template>

<script setup>
const { company } = defineProps({
  company: {
    type: Object,
    required: true,
    validator: (value) => {
      const requiredList = [
        "name",
        "country",
        "city",
        "address",
        "website",
        "kvk",
        "btw",
      ];
      return requiredList.every((key) => key in value);
    },
  },
  showEdit: {
    type: Boolean,
    default: true,
  },
});

const isLoading = ref(false);

async function handleEditBusiness() {
  // console.log("Edit business");
}
</script>

<style lang="scss" scoped>
.list-item {
  @apply grid grid-cols-2;
}
</style>
