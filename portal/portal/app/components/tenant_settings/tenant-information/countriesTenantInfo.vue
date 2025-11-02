<template>
  <section class="">
    <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("Operational Countries") }}</h3>
    <div class="my-3 grid grid-cols-2 gap-4">
      <span class="col-span-2">
        <UIVSelect
          name="tenant_countries"
          :placeholder="$t('Select Country')"
          :options="countries"
          :model-value="''"
          @update:model-value="UpdateOperationCountries($event)"
        />
      </span>

      <span class="col-span-2">
        <div
          v-for="(country, index) in dynamicTenant.operation_countries"
          :key="country.id"
          class="mt-2 flex w-full items-center justify-between rounded bg-theme-300 px-5 py-2"
        >
          <h3 class="text-white">
            {{ country.name }}
          </h3>
          <button class="text-red-500" @click="removeCountry(index)">
            <font-awesome-icon :icon="['fal', 'trash']" />
          </button>
        </div>
      </span>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  dynamicTenant: {
    type: Object,
    required: true,
  },
  countries: {
    type: Array,
    required: true,
  },
});

const UpdateOperationCountries = async (value) => {
  const newCountry = {
    id: value.value,
    name: value.label,
  };

  if (props.dynamicTenant.operation_countries?.some((c) => c.id === newCountry.id)) {
    return;
  }

  props.dynamicTenant.operation_countries.push({
    id: value.value,
    name: value.label,
  });
};

const removeCountry = (index) => {
  props.dynamicTenant.operation_countries.splice(index, 1);
};
</script>

<style scoped></style>
