<template>
  <div v-if="allCountries.length === 0" class="input p-1">
    <UILoader class="text-base" />
  </div>
  <UIVSelect
    v-else
    v-model="countryId"
    :name="name"
    :options="options"
    :reduce="(option) => option.value"
    :disabled="disabled"
  />
</template>

<script setup>
defineProps({
  name: {
    type: String,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const { t: $t } = useI18n();

const countryId = defineModel({ type: [Number, String, undefined], default: undefined });

const { data: allCountries } = await useLazyAPI("/countries", {
  transform: ({ data }) => data.map((country) => ({ label: country.name, value: country.id })),
  default: () => [],
});

const options = computed(() => {
  const _allCountries = [...allCountries.value];
  _allCountries.unshift({ label: $t("Choose a country"), value: "0" });
  return _allCountries;
});
</script>
